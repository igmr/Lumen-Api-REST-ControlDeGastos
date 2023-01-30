<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OperationController extends Controller
{
    //* -----------------------------------------------------------------------
    //* Methods HTTP
    //* -----------------------------------------------------------------------
    public function index(Request $request)
    {
        //* *******************************************************************
        //* Queries
        //* *******************************************************************
        $data = $this->findAll($request);
        //* *******************************************************************
        //* Response
        //* *******************************************************************
        return Response()->json($data);
    }
    public function show(int $id)
    {
        //* *******************************************************************
        //* Queries
        //* *******************************************************************
        $operation = $this->findOne($id);
        //* *******************************************************************
        //* Response
        //* *******************************************************************
        return Response()->json($operation);
    }
    public function storeIncome(Request $request)
    {
        //* *******************************************************************
        //* Validations
        //* *******************************************************************
        //* Validar request body.
        $valid = $this->validateStore($request);
        if($valid->fails())
        {
            return Response()->json($valid->errors(), 400);
        }
        //* *******************************************************************
        //* Response - create record
        //* *******************************************************************
        return $this->attach($request, true);
    }
    public function storeOutcome(Request $request)
    {
        //* *******************************************************************
        //* Validations
        //* *******************************************************************
        //* Validar request body.
        $valid = $this->validateStore($request);
        if($valid->fails())
        {
            return Response()->json([$valid->errors()], 400);
        }
        //? Validar que no sea de tipo income (ingreso)
        $subclassification_id  = $request->subclassification ?: 2;
        if($subclassification_id <2)
        {
            return Response()->json(["message" => "Operation rejected (2)"], 400);
        }
        //? Validar subclassification
        $subclassification = $this->countSubclassificationById($subclassification_id);
        if($subclassification == 0)
        {
            return Response()->json(["subclassification" => "Is is invalid"], 400);
        }
        //? Validar saldo actual
        $balance = $this->getBalance();
        $amount = abs($request->amount);
        $result = $balance - $amount;
        if($result < 0)
        {
            return Response()->json(["message" => "Insufficient balance"], 400);
        }
        //* *******************************************************************
        //* Response - create record
        //* *******************************************************************
        return $this->attach($request);
    }
    public function update(Request $request, int $id)
    {
        //* *******************************************************************
        //* Validations
        //* *******************************************************************
        $valid = $this->validateUpdate($request);
        if($valid->fails())
            return Response()
                ->json($valid->errors(),400);
        if($id <= 0)
            return Response()
                ->json(['message' => 'Operation rejected (1)'], 400);
        if(count($request->all()) === 0)
            return Response()
                ->json(['message' => 'Operation rejected (2)'], 400);
        //!	Validación de subclasificación
        $subclassification_id = $request->subclassification?:1;
        if($subclassification_id <= 0 || $subclassification_id == 2)
            return Response()
                ->json(['message' => 'Operation rejected (3)'], 400);
        $subclassification = $this->countSubclassificationById($subclassification_id);
        if($subclassification == 0)
            return Response()
                ->json(['message' => 'Operation rejected (4)'], 400);
        if(isset($request->subclassification))
        {
            $operation = $this->findOne($id);
            if(is_null($operation))
                return Response()
                    ->json(['message' => 'Operation rejected (5)'], 400);
            if($operation->type === 'income')
                return Response()
                    ->json(['message' => 'Operation rejected (6)'], 400);
        }
        //* ***********************************************************************
        //* Response - update record
        //* ***********************************************************************
        return $this->edit($request, $id);
    }
    public function destroy()
    {
        //* ***********************************************************************
        //* Validations
        //* ***********************************************************************
        $operation = new \App\Models\Operation;
        $operation = $operation::OrderByDesc('id')->first() ?: null;
        if(is_null($operation))
            return Response()
            ->json(['message' => 'Operation rejected (1)'], 400);
        //* ***********************************************************************
        //* Queries
        //* ***********************************************************************
        $deleted = $operation->delete();
        if($deleted)
        {
            return Response()
                ->json(['message'=> 'Remove successfully']);
        }
        //* *******************************************************************
        //* Response
        //* *******************************************************************
        return Response()
            ->json(['message' => 'Operation rejected (2)'], 500);
    }
    //* -----------------------------------------------------------------------
    //* End Methods HTTP
    //* -----------------------------------------------------------------------
    //* Queries
    //* -----------------------------------------------------------------------
    private function findAll(Request $request)
    {
        //? Obtener parámetro de búsqueda
        $search = $request->has("search")? $request->search : "";
        //? Obtener parámetro de paginación
        $pagination = $request->has("pagination") ? $request->pagination : 1;
        //? Obtener parámetro de numero de registro
        $records = $request->has("records") ? $request->records : 10;
        //* Instancia objeto de tipo classification.
        $operation = new \App\Models\Operation;
        if($pagination == 1)
        {
            $data = $operation::select(["operations.id",
                    "subclassification_id", "subclassifications.name AS subclassification",
                    "classification_id" ,"classifications.name AS classification",
                    "type", "amount", "operations.description", "operations.created_at"])
                ->join("subclassifications", "subclassifications.id", "=", "subclassification_id")
                ->join("classifications", "classifications.id", "=", "classification_id")
                ->where("operations.description", "like", "%" . $search . "%")
                ->orWhere("subclassifications.name", "like", "%" . $search . "%")
                ->orWhere("classifications.name", "like", "%" . $search . "%")
                ->orderByDesc("operations.id")
                ->paginate($records);
            if($request->has("search"))
                $data->appends(["search" => $search]);
            return $data;
        }
        return $operation::select(["operations.id",
                "subclassification_id", "subclassifications.name AS subclassification",
                "classification_id" ,"classifications.name AS classification",
                "type", "amount", "operations.description", "operations.created_at"])
            ->join("subclassifications", "subclassifications.id", "=", "subclassification_id")
            ->join("classifications", "classifications.id", "=", "classification_id")
            ->where("operations.description", "like", "%" . $search . "%")
            ->orWhere("subclassifications.name", "like", "%" . $search . "%")
            ->orWhere("classifications.name", "like", "%" . $search . "%")
            ->orderByDesc("operations.id")
            ->get();
    }
    private function findOne(int $id)
    {
        //* Instancia objeto de tipo operation.
        $operation = new \App\Models\Operation;
        //* Regresar operation.
        return $operation::select(["operations.id",
                "subclassification_id", "subclassifications.name AS subclassification",
                "classification_id" ,"classifications.name AS classification",
                "type", "amount", "operations.description", "operations.created_at"])
            ->join("subclassifications", "subclassifications.id", "=", "subclassification_id")
            ->join("classifications", "classifications.id", "=", "classification_id")
            ->where("operations.id", $id)
            ->firstOrFail();
    }
    private function attach(Request $request , bool $income = false)
    {
        //* Instancia objeto de tipo operation.
        $operation = new \App\Models\Operation;
        //* Llenar objecto.
        $operation->subclassification_id = $request->subclassification ?: 2;
        $operation->type = $income ? "income" : "outcome";
        $operation->amount = abs($request->amount) *-1;
        if($income)
        {
            $operation->subclassification_id = 1;
            $operation->amount = abs($request->amount);
        }
        $operation->description = $request->description ?: null;
        //* Ejecutar query.
        $operation->save();
        //* Responder al cliente.
        return Response()->json($operation, 201);
    }
    private function edit(Request $request, int $id)
    {
        //* Instancia objeto de tipo operation
        $operation = new \App\Models\Operation;
        //* Buscar operation
        $operation = $operation::find($id);
        //* Validar si existe operation
        if(is_null($operation))
        {
            return Response()->json(["message" => "operation rejected"], 400);
        }
        //? Validar que sea tipo outcome
        if($operation->type !== 'income')
        {
            //? Validar si existe campo subclassification en request body.
            if($request->has('subclassification'))
            {
                //? Validar que sea de tipo outcome
                if((int) $request->subclassification > 1)
                    $operation->subclassification_id = $request->subclassification;
            }
        }
        //? Validar si existe campo description en request body.
        if($request->has('description'))
        {
            $operation->description = $request->description ?: null;
        }
        //* ejecutar query.
        $operation->save();
        //* Responder al cliente.
        return Response()->json($operation);
    }
    private function countSubclassificationById(int $id)
    {
        $subclassification = new \App\Models\Subclassification;
        return $subclassification::where("id", $id)
            ->count();
    }
    private function getBalance ()
    {
        return \App\Models\Operation::sum("amount");
    }
    //* -----------------------------------------------------------------------
    //* End Queries
    //* -----------------------------------------------------------------------
    //* Validations
    //* -----------------------------------------------------------------------
    private function validateStore(Request $request)
    {
        //* Obtener datos de request body.
        $payload = $request->all();
        //* Definir reglas
        $rules = [
            "subclassification"  =>  "numeric",
            "amount"             =>  "required|numeric",
            "description"        =>  "max:255",
        ];
        //* Definir mensajes de error personalizados.
        $messageRules = [
            "numeric"  =>  "Must be numeric",
            "required" =>  "It is required",
            "max"      =>  "Must be less than :max",
        ];
        //* Ejecutar validaciones
        return Validator::make($payload, $rules, $messageRules);
    }
    private function validateUpdate(Request $request)
    {
        //* Obtener datos de request body
        $payload = $request->all();
        //* Definir reglas
        $rules = [
            'subclassification' =>  'numeric',
            'description'       =>  'max:255',
        ];
        //* Definir mensajes de error personalizados.
        $messageRules = [
            'numeric'   =>  'Must be numeric.',
            'max'       =>  'Must be less that :max.',
        ];
        //* Ejecutar validaciones
        return Validator::make($payload, $rules,$messageRules);
    }
    //* -----------------------------------------------------------------------
    //* End Validations
    //* -----------------------------------------------------------------------
}

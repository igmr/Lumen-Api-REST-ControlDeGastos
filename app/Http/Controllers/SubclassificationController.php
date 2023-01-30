<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubclassificationController extends Controller
{
    //* -----------------------------------------------------------------------
    //* Methods HTTP
    //* -----------------------------------------------------------------------
    public function index(Request $request)
    {
        //* *******************************************************************
        //* Queries
        //* *******************************************************************
        //* Regresa lista de subclassifications
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
        //* Regresa subclassification
        $subclassification = $this->findOne($id);
        //* *******************************************************************
        //* Response
        //* *******************************************************************
        return Response()->json($subclassification);
    }
    public function store(Request $request)
    {
        //* *******************************************************************
        //* Validations
        //* *******************************************************************
        //? Validar request body.
        $valid = $this->validateStore($request);
        if($valid->fails())
        {
            return Response()->json($valid->errors(), 400);
        }
        //? Validar si existe classification
        $classification_id = $request->classification ?: 2;
        $classification = $this->countClassificationById($classification_id);
        if(is_null($classification))
        {
            return Response()->json(["classification" => "Is is invalid"], 400);
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
        //? Validar request body.
        $valid = $this->validateUpdate($request, $id);
        if($valid->fails())
        {
            return Response()->json(["message" => "Operation rejected (1)"], 400);
        }
        //? Denegar operación para los primeros 2 registros.
        if($id < 2)
        {
            return Response()->json(["message" => "Operation rejected (2)"], 400);
        }
        //? Validar si existe classification
        $classification_id = $request->classification;
        if(isset($classification_id))
        {
            $classification = $this->countClassificationById($classification_id);
            if($classification == 0)
            {
                return Response()->json(["classification" => "Is is invalid"], 400);
            }
        }
        //? Validar numero de registros del request body
        if(count($request->all()) === 0)
        {
            return Response()->json(["message" => "Operation rejected (3)"], 400);
        }
        //* *******************************************************************
        //* Response - edit record
        //* *******************************************************************
        return $this->edit($request, $id);
    }
    public function destroy(int $id)
    {
        //* *******************************************************************
        //* Validations
        //* *******************************************************************
        //* Denegar operación para los primeros 2 registros
        if($id < 2)
        {
            return Response()->json(["message" => "Operation rejected (1)"],400);
        }
        //? Validar si existe relación con la tabla operations
        $operation = $this->countOperationById($id);
        if($operation > 0)
        {
            return Response()->json(["message" => "Operation rejected (2)"],400);
        }
        //? Validar si existe registro
        $subclassification = \App\Models\Subclassification::find($id)?: null;
        if(is_null($subclassification))
        {
            return Response()->json(["message" => "Operation rejected (3)"],400);
        }
        //* *******************************************************************
        //* Response - remove record
        //* *******************************************************************
        //* Eliminar registro
        return $this->remove($subclassification);
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
        //* Instancia objeto de tipo subclassification
        $subclassification = new \App\Models\Subclassification;
        //* Validar paginación
        if($pagination == 1)
        {
            //* Regresa lista de subclassifications con paginación
            return $subclassification::select(["subclassifications.id", "subclassifications.name", "subclassifications.description",
                    "classification_id", "classifications.name as classification"])
                ->join("classifications", "classification_id", "=", "classifications.id")
                ->where("subclassifications.id", ">" , 1)
                ->where("subclassifications.name", "like", "%". $search ."%")
                ->orWhere("subclassifications.description", "like", "%". $search ."%")
                ->orWhere("classifications.name", "like", "%". $search ."%")
                ->paginate($records);
        }
        //* Regresa lista de subclassifications
        return $subclassification::select(["subclassifications.id", "subclassifications.name", "subclassifications.description",
                "classification_id", "classifications.name as classification"])
            ->join("classifications", "classification_id", "=", "classifications.id")
            ->where("subclassifications.id", ">" , 1)
            ->where("subclassifications.name", "like", "%". $search ."%")
            ->orWhere("subclassifications.description", "like", "%". $search ."%")
            ->orWhere("classifications.name", "like", "%". $search ."%")
            ->get();
    }
    private function findOne(int $id)
    {
        //* Instancia objecto de tipo subclassification
        $subclassification = new \App\Models\Subclassification;
        //* Regresar subclassification
        return $subclassification::select(["subclassifications.id", "subclassifications.name", "subclassifications.description",
                "classification_id", "classifications.name as classification"])
            ->join("classifications", "classification_id", "=", "classifications.id")
            ->where("subclassifications.id", ">" , 1)
            ->where("subclassifications.id", $id)
            ->firstOrFail();
    }
    private function attach(Request $request)
    {
        //* Crear objecto de tipo subclassification.
        $subclassification = new \App\Models\Subclassification;
        //* Llenar objeto.
        $subclassification->classification_id = $request->classification ?: 2;
        $subclassification->name = $request->name;
        $subclassification->description = $request->has("description") ? $request->description : null;
        $subclassification->icon = $request->has("icon") ? $request->icon : null;
        //* Ejecutar query.
        $subclassification->save();
        //* Responder al cliente.
        return Response()->json($subclassification, 201);
    }
    private function edit(Request $request, int $id)
    {
        //* Buscar registro ha actualizar.
        $subclassification = \App\Models\Subclassification::find($id);
        //* Validar si existe subclassification
        if(is_null($subclassification))
        {
            return Response()->json(["message" => "Not found"],400);
        }
        //? Validar si existe campo classification en request body.
        if($request->has("classification"))
        {
            $subclassification->classification_id = $request->classification;
        }
        //? Validar si existe campo name en request body.
        if($request->has("name"))
        {
            $subclassification->name = $request->name;
        }
        //? Validar si existe campo description en request body.
        if($request->has("description"))
        {
            $subclassification->description = $request->description;
        }
        //? Validar si existe campo icon en request body.
        if($request->has("icon"))
        {
            $subclassification->icon = $request->icon;
        }
        //* Ejecutar query.
        $subclassification->save();
        //* Responser al cliente.
        return Response()->json($subclassification);
    }
    private function remove(\App\Models\Subclassification $subclassification)
    {
        //* ejecutar query.
        $deleted = $subclassification->delete();
        //? Validar respuesta.
        if($deleted)
        {
            //* Responder al cliente.
            return Response()->json(["message" => "Removed successfully"]);
        }
        //* Responder al cliente.
        return Response()->json(["message" => "Operation rejected (4)"], 400);
    }
    private function countClassificationById(int $id)
    {
        //* Instancia de tabla classifications
        $classification = new \App\Models\Classification;
        //* Regresa el número de registros encontrados
        return $classification::where("id", $id)
            ->where("id", ">", 1)
            ->count();
    }
    private function countOperationById(int $id)
    {
        //* Instancia de tabla operations
        $operation = new \App\Models\Operation;
        //* Regresa el número de registros encontrados
        return $operation::where("subclassification_id", $id)
            ->count();
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
            "classification"  => "required|numeric",
            "name"            => "required|unique:subclassifications|max:45",
            "description"     => "max:255",
            "icon"            => "max:65",
        ];
        //* Definir mensajes de error personalizados.
        $messageRules = [
            "required"      => "Is is required",
            "numeric"       => "Must be numeric",
            "unique"        => "Must be unique",
            "max"           => "Must be less than :max",
        ];
        //* Ejecutar validaciones
        return Validator::make($payload, $rules, $messageRules);
    }
    private function validateUpdate(Request $request, int $id)
    {
        //* Obtener datos de request body.
        $payload = $request ->all();
        //* Definir reglas
        $rules = [
            "classification"    => "numeric",
            "name"              => "max:45|unique:subclassifications,name,".$id,
            "description"       => "max:255",
            "icon"              => "max:65",
        ];
        //* Definir reglas de errores personalizados.
        $messageRules = [
            "numeric"   => "Must be numeric",
            "unique"    => "Must be unique",
            "max"       => "Must be less than :max",
        ];
        //* Ejecutar validaciones.
        return Validator::make($payload, $rules, $messageRules);
    }
    //* -----------------------------------------------------------------------
    //* End Validations
    //* -----------------------------------------------------------------------
}

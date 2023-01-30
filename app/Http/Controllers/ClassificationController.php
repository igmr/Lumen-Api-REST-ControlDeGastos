<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClassificationController extends Controller
{
    //* -----------------------------------------------------------------------
    //* Methods HTTP
    //* -----------------------------------------------------------------------
    public function index(Request $request)
    {
        //* *******************************************************************
        //* Queries
        //* *******************************************************************
        //* Regresar lista de classifications
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
        //* Regresar classification
        $classification = $this->findOne($id);
        //* *******************************************************************
        //* Response
        //* *******************************************************************
        return Response()->json($classification);
    }
    public function store(Request $request)
    {
        //* *******************************************************************
        //* Validations
        //* *******************************************************************
        //? Validar datos
        $valid = $this->validateStore($request);
        if($valid->fails())
        {
            return Response()->json($valid->errors(), 400);
        }
        //* *******************************************************************
        //* Response - create record
        //* *******************************************************************
        //* Crear registro
        return $this->attach($request);
    }
    public function update(Request $request, int $id)
    {
        //* *******************************************************************
        //* Validations
        //* *******************************************************************
        //? Validar request body
        $valid = $this->validateUpdate($request, $id);
        if($valid->fails())
        {
            return Response()->json($valid->errors(), 400);
        }
        //? Denegar operación para los primeros 2 registros.
        if($id < 2)
        {
            return Response()->json(["message" => "Operation reject (1)"], 400);
        }
        //? Validar numero de registros del request body
        if(count($request->all())=== 0)
        {
            return Response()->json(["message" => "Operation reject (2)"], 400);
        }
        //* *******************************************************************
        //* Response - edit record
        //* *******************************************************************
        //* editar registro
        return $this->edit($request, $id);
    }
    public function destroy(int $id)
    {
        //* *******************************************************************
        //* Validations
        //* *******************************************************************
        //? Denegar operación para eliminar los primeros 2 registros.
        if($id < 2)
        {
            return Response()->json(["message" => "Operation reject (1)"], 400);
        }
        //? Validar si existe relación con la tabla subclassification
        $subclassification = $this->countSubclassificationById($id);
        if($subclassification > 0)
        {
            return Response()->json(["message" => "Operation rejected (2)"], 400);
        }
        //? Validar si existe registro
        $classification = \App\Models\Classification::find($id);
        if(is_null($classification))
        {
            return Response()->json(["message"=> "Operation rejected (3)"], 400);
        }
        //* *******************************************************************
        //* Response - remove record
        //* *******************************************************************
        //* eliminar registro
        return $this->remove($classification);
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
        $classification = new \App\Models\Classification;
        //* Validar paginación
        if($pagination == 1)
        {
            //* Regresar lista de classification con paginación.
            return $classification::select(["id", "name", "description"])
                ->where("id", ">", 1)
                ->where("name", "like", "%".$search."%")
                ->orWhere("description", "like", "%". $search ."%")
                ->paginate($records);
        }
        //* Regresar lista de classification.
        return $classification::select(["id", "name", "description"])
            ->where("id", ">", 1)
            ->where("name", "like", "%".$search."%")
            ->orWhere("description", "like", "%". $search ."%")
            ->get();
    }
    private function findOne(int $id)
    {
        //* Crear nuevo objeto de tipo classification.
        $classification = new \App\Models\Classification;
        //* Regresar classification.
        return $classification::select(["id", "name", "description"])
            ->where("id", ">", 1)
            ->where("id", $id)
            ->firstOrFail();
    }
    private function attach(Request $request)
    {
        //* Instancia objecto de tipo Classification.
        $classification = new \App\Models\Classification;
        //* Llenar objeto.
        $classification->name = $request->name;
        $classification->description = $request->description ?: null;
        $classification->icon = $request->icon ?: null;
        //* Ejecutar query.
        $classification->save();
        //* Responser al cliente.
        return Response()->json($classification, 201);
    }
    private function edit(Request $request, int $id)
    {
        //* Buscar registro ha actualizar.
        $classification = \App\Models\Classification::find($id);
        //? Validar si existe classification
        if(is_null($classification))
        {
            return Response()->json(["message" => "Not found"],400);
        }
        //? Validar si existe campo name en request body.
        if($request->has("name"))
        {
            $classification->name = $request->name;
        }
        //? Validar si existe campo description en request body.
        if($request->has("description"))
        {
            $classification->description = $request->description;
        }
        //? Validar si existe campo icon en request body.
        if($request->has("icon"))
        {
            $classification->icon = $request->icon;
        }
        //* Ejecutar query.
        $classification->save();
        //* Responder al cliente.
        return Response()->json($classification);
    }
    private function remove(\App\Models\Classification $classification)
    {
        //* Ejecutar query.
        $delete = $classification->delete();
        //? Validar repuesta.
        if($delete)
        {
            //* Responder al cliente.
            return Response()->json(["message" => "Removed successfully"]);
        }
        //* Responder al cliente.
        return Response()->json(["message" => "Operation fail"], 400);
    }
    private function countSubclassificationById(int $id)
    {
        //* Obtener instancia de tabla Subclassification
        $subclassification = new \App\Models\Subclassification;
        //* Regresa un número registros encontrados.
        return $subclassification::where("classification_id", $id)
            ->where("id", ">", 2)
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
        //* Definir reglas.
        $rules = [
            "name"        => "required|unique:classifications|max:45",
            "description" => "max:255",
            "icon"        => "max:65",
        ];
        //* Definir mensajes de error personalizados.
        $rulesMessage = [
            "required"  => "It is required",
            "unique"    => "Must be unique",
            "max"       => "Must be less than :max",
        ];
        //* Ejecutar validaciones.
        return Validator::make($payload, $rules, $rulesMessage);
    }
    private function validateUpdate(Request $request, int $id)
    {
        //* Obtener datos de request body
        $payload = $request->all();
        //* Definir reglas.
        $rules = [
            "name"        => "max:45|unique:classification,name".$id,
            "description" => "max:255",
            "icon"        => "max:65",
        ];
        //* Definir mensajes de error personalizados.
        $rulesMessage = [
            "unique"    => "Must be unique",
            "max"       => "Must be less than :max",
        ];
        //* Ejecutar validaciones.
        return Validator::make($payload, $rules, $rulesMessage);
    }
    //* -----------------------------------------------------------------------
    //* End Validations
    //* -----------------------------------------------------------------------
}

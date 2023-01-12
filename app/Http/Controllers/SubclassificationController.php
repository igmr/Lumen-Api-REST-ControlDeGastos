<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class SubclassificationController extends Controller
{
	//* ***********************************************************************
	//* METHODS HTTP
	//* ***********************************************************************
	public function index()
	{
		//* *******************************************************************
		//* Queries
		//* *******************************************************************
		$data = $this->findAll();
		return Response()->json($data);
	}
	public function store(Request $request)
	{
		//* ***********************************************************************
		//* Validation
		//* ***********************************************************************
		$valid = $this->validateStore($request);
		if($valid->fails())
			return Response()->json($valid->errors(), 400);
		$classification_id = $request->classification ?: 1;
		if($classification_id > 1)
		{
			$classification = $this->findClassificationsById($classification_id);
			if(is_null($classification))
				return Response()
					->json(['classification'=> 'Is is invalid'], 400);
		}
		//* ***********************************************************************
		//* Queries
		//* ***********************************************************************
		return $this->attach($request);
	}
	public function show(int $id)
	{
		//* *******************************************************************
		//* Queries
		//* *******************************************************************
		$data = $this->findOne($id);
		return Response()->json($data);
	}
	public function update(Request $request, int $id)
	{
		//* *******************************************************************
		//* Validation
		//* *******************************************************************
		$valid = $this->validateUpdate($request);
		if($valid->fails())
			return Response()->json($valid->errors(),400);
		if($id <= 2)
			return Response()
				->json(['message' => 'Operation rejected (1)'], 400);
		if(count($request->all()) === 0)
			return Response()
				->json(['message' => 'Operation rejected (2)'], 400);
		$classification_id = $request->classification;
		if(!empty($classification_id) || !is_null($classification_id))
		{
			$classification = $this
				->findClassificationsById($classification_id);
			if(is_null($classification))
				return Response()
					->json(['classification'=> 'Is is invalid'], 400);
		}
		//* *******************************************************************
		//* Queries
		//* *******************************************************************
		return $this->edit($request, $id);
	}
	public function destroy(int $id)
	{
		//* ***********************************************************************
		//* Validation
		//* ***********************************************************************
		if($id < 2)
			return Response()
				->json(['message' => 'Operation rejected (1)'], 400);
		$operation = $this->findOperationById($id) ?: null;
		if(is_null($operation))
			return Response()
				->json(['message' => 'Operation rejected (2)'], 500);
		$subclassification = \App\Models\Subclassification::find($id) ?: null;
		if(is_null($subclassification))
			return Response()
				->json(['message' => 'Operation rejected (3)'], 500);
		//* ***********************************************************************
		//* Queries
		//* ***********************************************************************
		return $this->remove($subclassification);
	}
	//* ***********************************************************************
	//* QUERIES
	//* ***********************************************************************
	private function findAll()
	{
		$subclassification = new \App\Models\Subclassification;
		return $subclassification::select([
			'id AS ID', 'classification_id AS classification','name',
			'description', 'icon'
			])->get();
	}
	private function findOne($id)
	{
		$subclassification = new \App\Models\Subclassification;
		return $subclassification::select([
			'id AS ID', 'classification_id AS classification','name',
			'description', 'icon'
			])->firstWhere('id', $id);
	}
	private function findClassificationsById(int $id)
	{
		$classification = new \App\Models\Classification;
		return $classification::find($id);
	}
	private function findOperationById(int $id)
	{
		$operation = new \App\Models\Operation;
		return $operation::where('subclassification_id', $id)->get();
	}
	private function attach(Request $request)
	{
		$subclassification = new \App\Models\Subclassification;
		$subclassification->classification_id = $request->classification;
		$subclassification->name = $request->name;
		$subclassification->description = $request->description ?: null;
		$subclassification->icon = $request->icon ?: null;
		$subclassification->save();
		return Response()->json($subclassification, 201);
	}
	private function edit(Request $request, int $id)
	{
		$subclassification = new \App\Models\Subclassification;
		$subclassification = $subclassification::find($id);
		if(!empty($request->classification ?: ''))
			$subclassification->classification_id = $request->classification;
		if(!empty($request->name ?: ''))
			$subclassification->name = $request->name;
		if(!empty($request->description ?: ''))
			$subclassification->description = $request->description;
		if(!empty($request->icon ?: ''))
			$subclassification->icon = $request->icon;
		$subclassification->save();
		return Response()
			->json($subclassification);
	}
	private function remove(\App\Models\Subclassification $subclassification)
	{
		$deleted = $subclassification->delete();
		if($deleted)
			return Response()
				->json(['message'=> 'Removed successfully']);
		return Response()
			->json(['message' => 'Operation rejected (4)'], 500);
	}
	//* ***********************************************************************
	//* VALIDATIONS
	//* ***********************************************************************
	private function validateStore(Request $request)
	{
		$payload = $request->all();
		$rules = [
			'classification'	=>	'required|numeric',
			'name'				=>	'required|unique:subclassifications|max:45',
			'description'		=>	'max:255',
			'icon'				=>	'max:65',
		];
		$messageRules = [
			'required'	=>	'It is required.',
			'numeric'	=>	'Must be numeric.',
			'unique'	=>	'Must be unique.',
			'max'		=>	'Must be less than :max.',
		];
		return validator::make($payload, $rules, $messageRules);
	}
	private function validateUpdate(Request $request)
	{
		$payload = $request->all();
		$rules = [
			'classification'	=>	'numeric',
			'name'				=>	'unique:subclassifications|max:45',
			'description'		=>	'max:255',
			'icon'				=>	'max:65',
		];
		$messageRules = [
			'numeric'	=>	'Must be numeric.',
			'unique'	=>	'Must be unique.',
			'max'		=>	'Must be less than :max.',
		];
		return validator::make($payload, $rules, $messageRules);
	}
}
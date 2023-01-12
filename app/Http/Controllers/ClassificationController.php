<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ClassificationController extends Controller
{
	//* ***********************************************************************
	//*	METHODS HTTP
	//* ***********************************************************************
	public function index()
	{
		//* *******************************************************************
		//*	Queries
		//* *******************************************************************
		$data = $this->findAll();
		return Response()->json($data);
	}
	public function store(Request $request)
	{
		//* *******************************************************************
		//*	Validation
		//* *******************************************************************
		$valid = $this->validateStore($request);
		if($valid->fails())
			return Response()->json($valid->errors(), 400);
		//* *******************************************************************
		//*	Queries
		//* *******************************************************************
		return  $this->attach($request);
	}
	public function show(int $id)
	{
		//* *******************************************************************
		//* Queries
		//* *******************************************************************
		$data = $this->findOne((int) $id);
		return Response()->json($data);
	}
	public function update(Request $request, int $id)
	{
		try
		{
			//* ***************************************************************
			//*	Validation
			//* ***************************************************************
			$valid = $this->validateUpdate($request, $id);
			if($valid->fails())
				return Response()->json($valid->errors(),400);
			if($id <= 2)
				return Response()
					->json(['message' => 'Operation rejected (1)'], 400);
			if(count($request->all()) === 0)
				return Response()
					->json(['message' => 'Operation rejected (2)'], 400);
			//* ***************************************************************
			//*	Queries
			//* ***************************************************************
			return $this->edit($request, $id);
		}
		catch (Exception $e)
		{
			return Response()->json(['message' => $e->getMessage()], 500);
		}
	}
	public function destroy(int $id)
	{
		//* *******************************************************************
		//*	Validation
		//* *******************************************************************
		if($id <= 2)
			return Response()
				->json(['message' => 'Operation rejected (1)'], 400);
		$subclassification = $this->findSubclassificationsById((int) $id);
		if(count($subclassification) > 0)
			return Response()
				->json(['message' => 'Operation rejected (2)'], 500);
		$classification = \App\Models\Classification::find($id);
		if(is_null($classification))
			return Response()
				->json(['message' => 'Operation rejected (3)'], 500);
		//* *******************************************************************
		//*	Queries
		//* *******************************************************************
		return $this->remove($classification);
	}
	//* ***********************************************************************
	//*	Queries
	//* ***********************************************************************
	private function findAll()
	{
		$classification = new \App\Models\Classification;
		return $classification::select(['id AS ID', 'name', 'description'])
			->get();
	}
	private function findOne(int $id)
	{
		$classification = new \App\Models\Classification;
		return $classification::select(['id AS ID', 'name', 'description'])
			->firstWhere('id', $id);
	}
	private function findSubclassificationsById(int $id)
	{
		$subclassification = new \App\Models\Subclassification;
		return $subclassification::where('classification_id', $id)->get();
	}
	private function attach(Request $request)
	{
		$classification = new \App\Models\Classification;
		$classification->name = $request->name;
		$classification->description = $request->description ?: '';
		$classification->icon = $request->icon ?: '';
		$classification->save();
		return Response()->json($classification, 201);
	}
	private function edit(Request $req, int $id)
	{
		$classification = \App\Models\Classification::find($id);
		if(!empty($req->name ?: ''))
			$classification->name= $req->name;
		if(!empty($req->description ?: ''))
			$classification->description= $req->description;
		if(!empty($req->icon ?: ''))
			$classification->icon= $req->icon;
		$classification->save();
		return Response()->json($classification);
	}
	private function remove(\App\Models\Classification $classification)
	{
		$deleted = $classification->delete();
		if($deleted)
			return Response()
				->json(['message'=> 'Removed successfully']);
		return Response()
			->json(['message' => 'Operation fail'], 400);

	}
	//* ***********************************************************************
	//*	Validation
	//* ***********************************************************************
	private function validateStore(Request $request)
	{
		$payload = $request->all();
		$rules = [
			'classification'	=>	'numeric',
			'name'				=>	'required|unique:classifications|max:45',
			'description'		=>	'max:255',
			'icon'				=>	'max:65'
		];
		$rulesMessage = [
			'required'	=>	'It is required.',
			'unique'	=>	'Must be unique.',
			'max'		=>	'Must be less than :max.',
			'numeric'	=>	'Must be numeric.',
		];
		return Validator::make($payload, $rules, $rulesMessage);
	}
	private function validateUpdate(Request $request)
	{
		$payload = $request->all();
		$rules = [
			'classification'	=>	'numeric',
			'name'				=>	'unique:classifications|max:45',
			'description'		=>	'max:255',
			'icon'				=>	'max:65',
		];
		$rulesMessage = [
			'unique'	=>	'Must be unique.',
			'max'		=>	'Must be less that :max.',
			'numeric'	=>	'Must be numeric.',
		];
		return Validator::make($payload, $rules, $rulesMessage);
	}
}

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
	public function index(Request $request)
	{
		//* *******************************************************************
		//*	Queries
		//* *******************************************************************
		return Response()->json($this->findAll($request));
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
		return Response()->json($this->findOne($id));
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
		catch (\Exception $e)
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
		$subclassification = $this->countSubclassificationsById((int) $id);
		if($subclassification > 0)
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
	private function findAll(Request $request)
	{
		$classification = new \App\Models\Classification;
		$pagination = $request->has('pagination') ? (int) $request->pagination : 1;
		$search = $request->has('search') ? $request->search : '';
		if($pagination == 1)
		{
			$data = $classification::select(['id AS ID', 'name', 'description'])
				->where('name','like', '%'.$search.'%')
				->orWhere('description', 'like', '%'.$search.'%')
				->Paginate(10);
			if($request->has('search'))
				$data->appends(['search' => $search]);
			return $data;
		}
		return $classification::select(['id AS ID', 'name', 'description'])
			->where('name','like', '%'.$search.'%')
			->orWhere('description', 'like', '%'.$search.'%')
			->get();
	}
	private function findOne(int $id)
	{
		$classification = new \App\Models\Classification;
		return $classification::select(['id AS ID', 'name', 'description'])
			->Where('id', $id)
			->firstOrFail();
	}
	private function countSubclassificationsById(int $id)
	{
		$subclassification = new \App\Models\Subclassification;
		return $subclassification::where('classification_id', $id)->count();
	}
	private function attach(Request $request)
	{
		$classification = new \App\Models\Classification;
		$classification->name = $request->name;
		$classification->description= $request->description ?: null;
		$classification->icon= $request->icon ?: null;
		$classification->save();
		return Response()->json($classification, 201);
	}
	private function edit(Request $request, int $id)
	{
		$classification = \App\Models\Classification::find($id);
		if($request->has('name'))
			$classification->name= $request->name;
		if($request->has('description'))
			$classification->description= $request->description;
		if($request->has('icon'))
			$classification->icon= $request->icon;
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
	private function validateUpdate(Request $request, int $id)
	{
		$payload = $request->all();
		$rules = [
			'classification'	=>	'numeric',
			'name'				=>	'max:45|unique:classifications,name,'.$id,
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

<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


class OperationController extends Controller
{
	//* ***********************************************************************
	//* HTTP
	//* ***********************************************************************
	public function index()
	{
		//* ***********************************************************************
		//* Queries
		//* ***********************************************************************
		$data = $this->findAll();
		return Response()
			->json($data);
	}

	public function storeOutcome(Request $request)
	{
		//* ***********************************************************************
		//* Validation
		//* ***********************************************************************
		$valid = $this->validateStore($request);
		if($valid->fails())
			return Response()->json($valid->errors(), 400);
		$subclassification_id = $request->subclassification ?:2;
		if((int) $subclassification_id < 2)
			return Response()
				->json(['message' => 'Operation rejected (1)'], 400);
		if((int) $subclassification_id > 2)
		{
			$subclassification = $this->
				findOneClassificationItem($subclassification_id)?:null;
			if(is_null($subclassification))
				return Response()
					->json(['message' => 'Operation rejected (2)'], 400);
		}
		$balance = $this->getBalance();
		$amount = abs($request->amount);
		$result = $balance - $amount;
		if($result < 0)
			return Response()
				->json(['message' => 'Insufficient balance'], 400);
		//* ***********************************************************************
		//* Queries
		//* ***********************************************************************
		return $this->attach($request);
	}

	public function storeIncome(Request $request)
	{
		//* *******************************************************************
		//* Validation
		//* *******************************************************************
		$valid = $this->validateStore($request);
		if($valid->fails())
			return Response()->json($valid->errors(), 400);
		//* *******************************************************************
		//* Queries
		//* *******************************************************************
		return $this->attach($request, true);
	}

	public function show($id)
	{
		try {
			//* ***********************************************************************
			//* Queries
			//* ***********************************************************************
			$data = $this->findOne((int) $id);
			return Response()->json($data);
		} catch (Exception $e) {
			return Response()->json([], 500);
		}
	}

	public function update(Request $request, int $id)
	{
		//* ***********************************************************************
		//* Validation
		//* ***********************************************************************
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
		$subclassification = $this->
			findSubclassificationById($subclassification_id)?:null;
		if(is_null($subclassification))
			return Response()
				->json(['message' => 'Operation rejected (4)'], 400);
		if(isset($request->subclassification))
		{
			$operation = $this->findOne($id);
			if(is_null($operation))
				return Response()
				->json(['message' => 'Operation rejected (5)'], 400);
			if($operation->type === 'ingreso')
				return Response()
					->json(['message' => 'Operation rejected (6)'], 400);
		}
		//* ***********************************************************************
		//* Queries
		//* ***********************************************************************
		return $this->edit($request, $id);
	}

	public function destroy()
	{
		//* ***********************************************************************
		//* Validation
		//* ***********************************************************************
		$operation = new \App\Models\Operation;
		$operation = $operation::OrderByDesc('id')->first()?:null;
		if(is_null($operation))
			return Response()
			->json(['message' => 'Operation rejected (1)'], 400);
		//* ***********************************************************************
		//* Queries
		//* ***********************************************************************
		$deleted = $operation->delete();
		if($deleted)
			return Response()
				->json(['message'=> 'Remove successfully']);
		return Response()
			->json(['message' => 'Operation rejected (2)'], 500);
	}
	//* ***********************************************************************
	//* Queries
	//* ***********************************************************************
	private function findAll()
	{
		$operation = new \App\Models\Operation;
		return $operation::select(['id AS ID',
				'subclassification_id AS subclassification',
				'type', 'amount', 'description'])
			->get();
	}
	private function findOne(int $id)
	{
		$operation = new \App\Models\Operation;
		return $operation::select(['id AS ID',
				'subclassification_id AS subclassification',
				'type', 'amount', 'description'])
			->firstWhere('id', $id);
	}
	private function attach(Request $request, bool $income = false)
	{
		$operation = new \App\Models\Operation();
		$operation->subclassification_id = $request->subclassification ?: 2;
		$operation->type = 'egreso';
        $operation->amount = abs($request->amount)* -1;
		if($income)
		{
			$operation->subclassification_id = 1;
			$operation->type = 'ingreso';
			$operation->amount = abs($request->amount);
		}
		$operation->description = $request->description ?: '';
		$operation->save();
		return Response()
			->json($operation, 201);
	}
	private function edit(Request $request, int $id)
	{
		$operation = new \App\Models\Operation;
		$operation = $operation::find($id);
		if(($request->subclassification?:-1) > 0)
			$operation->subclassification_id = $request->subclassification;
		if(!empty($request->description?:''))
			$operation->description = $request->description;
		$operation->save();
		return Response()
			->json($operation);
	}
	private function findSubclassificationById(int $id)
	{
		$subclassification = new \App\Models\Subclassification;
		return $subclassification::find($id);
	}
	private function getBalance()
	{
		$operation = new \App\Models\Operation;
		return $operation::sum('amount');
	}
	//* ***********************************************************************
	//* Validation
	//* ***********************************************************************
	private function validateStore(Request $request)
	{

		$payload = $request->all();
		$rules = [
			'subclassification'	=>	'numeric',
			'amount'			=>	'required|numeric',
			'description'		=>	'max:255',
		];
		$rulesMessage = [
			'numeric'	=>	'Must be numeric.',
			'required'	=>	'It is required.',
			'max'		=>	'Must be less that :max.',
		];
		return Validator::make($payload, $rules,$rulesMessage);
	}
	private function validateUpdate(Request $request)
	{
		$payload = $request->all();
		$rules = [
			'subclassification'	=>	'numeric',
			'description'		=>	'max:255',
		];
		$rulesMessage = [
			'numeric'	=>	'Must be numeric.',
			'max'		=>	'Must be less that :max.',
		];
		return Validator::make($payload, $rules,$rulesMessage);
	}
}

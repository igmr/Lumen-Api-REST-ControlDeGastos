<?php
namespace Tests;

class OperationTest extends TestCase
{
	private $base_api = '/api/operation';
	private $response_json_post_put = [
		'subclassification_id',
		'type',
		'amount',
		'description',
		'updated_at',
		'created_at',
		'id',
	];
	private $response_get = [
		'ID',
		'subclassification',
		'type',
		'amount',
		'description',
	];
	// [GET] /api/operation
	public function testShouldReturnAllOperations()
	{
		$response = $this->get($this->base_api);
		$response->seeStatusCode(200);
		$response->seeJsonStructure(['*' => $this->response_get]);
	}
	// [GET] /api/operation/{id}
	public function testShouldReturnOneOperation()
	{
		$response = $this->get($this->base_api . '/2');
		$response->seeStatusCode(200);
		$response->seeJsonStructure($this->response_get);
	}
	// [POST] /api/operation/income
	public function testShouldCreateOperationIncome()
	{
		$payload = ['amount' => 100,'description' => 'PHPUnit income',];
		$response = $this->post($this->base_api . '/income', $payload);
		$response->seeStatusCode(201);
		$response->seeJsonStructure($this->response_json_post_put);
	}
	// [POST] /api/operation/income
	public function testShouldCreateOperationOutcome()
	{
		$payload = ['amount' => 1,'description' => 'PHPUnit outcome',];
		$response = $this->post($this->base_api . '/outcome', $payload);
		$response->seeStatusCode(201);
		$response->seeJsonStructure($this->response_json_post_put);
	}
	// [PUT] /api/operation/{id}
	public function testShouldUpdateOperation()
	{
		$payload = ['description' => 'PHPUnit updated '. rand(0,50)];
		$response = $this->put($this->base_api. '/3', $payload);
		$response->seeStatusCode(200);
		$response->seeJsonStructure($this->response_json_post_put);
	}
	// [DELETE] /api/operation/{id}
	public function testShouldDeleteOperation()
	{
		$response = $this->delete($this->base_api);
		$response->seeStatusCode(200);
		$response->seeJsonEquals(['message' => 'Remove successfully']);
	}
}
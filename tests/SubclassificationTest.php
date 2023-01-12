<?php
namespace Tests;

class SubclassificationTest extends TestCase
{
	private $base_api = '/api/subclassification';
	private $response_json_post_put = [
		'name',
		'description',
		'icon',
		'updated_at',
		'created_at',
		'id',
		'classification_id'
	];
	private $response_get = [
		'ID',
		'name',
		'description',
		'classification',
	];
	// [GET] /api/subclassification
	public function testShouldReturnAllSubclassifications()
	{
		$response = $this->get($this->base_api);
		$response->seeStatusCode(200);
		$response->seeJsonStructure(['*' => $this->response_get]);
	}
	// [GET] /api/subclassification/{id}
	public function testShouldReturnOneSubclassifications()
	{
		$response = $this->get($this->base_api . '/2');
		$response->seeStatusCode(200);
		$response->seeJsonStructure($this->response_get);
	}
	// [POST] /api/subclassification
	public function testShouldCreateSubclassification()
	{
		$payload = ['classification' => 2,'name' => 'sc00 '. rand(0,10000),];
		$response = $this->post($this->base_api, $payload);
		$response->seeStatusCode(201);
		$response->seeJsonStructure($this->response_json_post_put);
	}
	// [PUT] /api/subclassification/{id}
	public function testShouldUpdateSubclassification()
	{
		$payload = ['description' => 'description '. rand(0,10000)];
		$response = $this->put($this->base_api. '/3', $payload);
		$response->seeStatusCode(200);
		$response->seeJsonStructure($this->response_json_post_put);
	}
	// [DELETE] /api/subclassification/{id}
	public function testShouldDeleteSubclassification()
	{
		$response = $this->delete($this->base_api. '/82');
		$response->seeStatusCode(500);
		$response->seeJsonEquals(['message' => 'Operation rejected (3)']);
	}
}
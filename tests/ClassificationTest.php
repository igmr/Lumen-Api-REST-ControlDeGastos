<?php
namespace Tests;

class ClassificationTest extends TestCase
{
	private $base_api = '/api/classification';
	private $response_get = [
		'ID',
		'name',
		'description',
	];
	private $response_json_post_put = [
		'name',
		'description',
		'icon',
		'updated_at',
		'created_at',
		'id',
	];
	// [GET] /api/classification
	public function testShouldReturnAllClassifications()
	{
		$response = $this->get($this->base_api);
		$response->seeStatusCode(200);
		$response->seeJsonStructure(['*' => $this->response_get]);
	}
	// [GET] /api/classification/{id}
	public function testShouldReturnOneClassifications()
	{
		$response = $this->get($this->base_api . '/2');
		$response->seeStatusCode(200);
		$response->seeJsonStructure($this->response_get);
	}
	// [POST] /api/classification
	public function testShouldCreateClassification()
	{
		$payload = ['name' => 'classification'. rand(0,10000)];
		$response = $this->post($this->base_api, $payload);
		$response->seeStatusCode(201);
		$response->seeJsonStructure($this->response_json_post_put);
	}
	// [PUT] /api/classification/{id}
	public function testShouldUpdateClassification()
	{
		$payload = ['description' => 'description '. rand(0,50)];
		$response = $this->put($this->base_api. '/3', $payload);
		$response->seeStatusCode(200);
		$response->seeJsonStructure($this->response_json_post_put);
	}
	// [DELETE] /api/classification/{id}
	public function testShouldDeleteClassification()
	{
		$response = $this->delete($this->base_api. '/20');
		$response->seeStatusCode(500);
		$response->seeJsonEquals(['message' => 'Operation rejected (3)']);
	}
}
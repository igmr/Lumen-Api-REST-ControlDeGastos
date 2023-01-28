<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
	protected $table= 'operations';
	protected $primaryKey = 'id';
	protected $fillable = [
		'id',
		'subclassifications_id',
		'type',
		'amount',
		'description',
	];

	protected $hidden = [
		'created_at',
		'updated_at',
	];
}

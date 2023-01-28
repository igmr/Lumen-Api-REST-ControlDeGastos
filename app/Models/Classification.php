<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classification extends Model
{
	use SoftDeletes;
	protected $table = "classifications";
	protected $primaryKey = 'id';
	protected $fillable = [
		'name',
		'description',
		'icon'
	];

	protected $hidden = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
}

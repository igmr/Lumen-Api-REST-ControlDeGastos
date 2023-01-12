<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subclassification extends Model
{
	use SoftDeletes;
	protected $table = "subclassifications";
	protected $primaryKey = 'id';
	protected $fillable = [
		'classification_id',
		'name',
		'description',
		'icon'
	];
}

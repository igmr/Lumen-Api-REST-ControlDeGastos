<?php

namespace App\Models;

use Illuminate\Database\eloquent\Model;


class Operation extends Model
{


    //* definir tabla.
    protected $table = "operations";
    //* Definir clave primaria.
    protected $primaryKey = "id";
    //* Campos visibles en consulta.
    protected $fillable = [
        "subclassification_id",
        "type",
        "amount",
        "description",
    ];
    //* Campos no visibles en consulta.
    protected $hidden = [
        "created_at",
        "updated_at",
    ];
}
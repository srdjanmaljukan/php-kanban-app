<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardColumn extends Model
{
    protected $table = 'columns'; // eksplicitno govorimo Eloquent-u pravo ime tabele

    protected $fillable = ['board_id', 'name', 'position'];

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function cards()
{
    return $this->hasMany(Card::class, 'column_id')->orderBy('position')->orderBy('id');
}
}
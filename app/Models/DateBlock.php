<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['entry_date', 'out_date', 'reason'])]
class DateBlock extends Model
{
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'out_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeOverlapping(Builder $query, DateTimeInterface $entry, DateTimeInterface $out, ?int $exceptId = null): Builder
    {
        return $query
            ->where('entry_date', '<', $out)
            ->where('out_date', '>', $entry)
            ->when($exceptId !== null, fn (Builder $query): Builder => $query->whereKeyNot($exceptId));
    }
}

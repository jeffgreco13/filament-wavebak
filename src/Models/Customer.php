<?php

namespace Jeffgreco13\FilamentWave\Models;

use Filament\Support\Facades\FilamentColor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\Color\Rgb;

class Customer extends Model
{
    protected $table = 'wave_customers';

    protected $keyType = 'string';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $casts = ['meta' => AsArrayObject::class, 'address' => 'array'];

    public $incrementing = false;

    public function scopeActive(Builder $query): void
    {
        $query->where('is_archived', false);
    }

    public function scopeArchived(Builder $query): void
    {
        $query->where('is_archived', true);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['first_name'].' '.$attributes['last_name'],
        );
    }

    protected function avatarUrl(): Attribute
    {
        $name = str($this->name)
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');
        $backgroundColor = Rgb::fromString('rgb('.FilamentColor::getColors()['gray'][950].')')->toHex();
        $url = 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=FFFFFF&background='.str($backgroundColor)->after('#');

        return Attribute::make(
            get: fn () => $url,
        );
    }

    public function archive()
    {
        $this->update(['is_archived' => true]);
    }

    public function unarchive()
    {
        $this->update(['is_archived' => false]);
    }

    public function toggleArchive()
    {
        if ($this->is_archived) {
            $this->unarchive();
        } else {
            $this->archive();
        }
    }
}

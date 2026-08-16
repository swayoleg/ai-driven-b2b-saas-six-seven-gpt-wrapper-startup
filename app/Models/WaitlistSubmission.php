<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class WaitlistSubmission extends Model
{
    use CrudTrait;

    protected $fillable = [
        'email',
        'company',
        'size',
        'urgency',
        'maturity',
        'pain',
        'budget',
        'position',
        'locale',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * The queue position is a stable hash of the email — the same arithmetic the
     * wizard used to do in the browser, moved here so the number is issued by the
     * server and stored alongside the submission.
     */
    public static function positionFor(string $email): int
    {
        $hash = 0;

        foreach (mb_str_split(mb_strtolower($email)) as $char) {
            $hash = ($hash * 31 + mb_ord($char)) % 100000;
        }

        return 6700 + $hash % 4200;
    }
}

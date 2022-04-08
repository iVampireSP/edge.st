<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use App\Exceptions\User\BalanceNotEnough;
use App\Exceptions\User\BalanceLockTimeout;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'api_token', 'email_verified_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    // ToDo 账户余额增改记录
    public static function addBalance($amount, $user_id = false)
    {
        if (!$user_id) {
            $user = auth()->user();
        } else {
            $user = self::find($user_id);
        }

        $result = true;

        $lock = Cache::lock("user_balance_" . $user->id, 5);
        $lock->block(5);
        try {
            $user->balance += $amount;
            $user->save();
        } catch (LockTimeoutException) {
            write('Unable to update user balance');
            throw new BalanceLockTimeout();
            $result = false;
        } finally {
            optional($lock)->release();
        }

        return $result;
    }

    public static function costBalance($amount, $user_id = false)
    {

        if (!$user_id) {
            $user = auth()->user();
        } else {
            $user = self::find($user_id);
        }

        $result = true;
        $lock = Cache::lock("user_balance_" . $user->id, 5);
        $lock->block(5);
        try {
            $user->balance -= $amount;
            if ($user->balance < 0) {
                $result = false;

                write('Balance not enough.', $user->id);
            }
        } catch (LockTimeoutException) {
            throw new BalanceLockTimeout();
            write('Unable to update user balance');
        } finally {
            $user->save();
            optional($lock)->release();
        }

        return $result;
    }

    // public static function costBalanceFail($amount, $user_id)
    // {
    //     $result = self::costBalance($amount, $user_id);

    //     if (!$result) {
    //         write('Balance not enough.', $user_id);
    //         throw new BalanceNotEnough('user balance not enough.');
    //     }
    // }


    // 用户购买产品
    public static function buyProduct($user_id, $product_id)
    {
    }
}

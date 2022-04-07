<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use App\Exceptions\User\BalanceLockTimeout;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
            // if ($user->balance < 0) {
            //     write('Balance not enough.');
            //     throw new BalanceNotEnough();
            // } else {
            //     $user->save();
            //     write('Order successfully updated.');
            //     write('Your balance is now: ' . $user->balance);
            //     $result = true;
            // }
        } catch (LockTimeoutException) {
            throw new BalanceLockTimeout();
            write('Unable to update user balance');
        } finally {
            $user->save();
            optional($lock)->release();
        }
        return $result;
    }


    // 用户购买产品
    public static function buyProduct($user_id, $product_id)
    {
    }
}

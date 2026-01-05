<?php namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table = 'login_attempts';
    protected $allowedFields = ['username_or_email','ip','success','created_at'];
    protected $useTimestamps = false;

    public function record($login, $ip, $success)
    {
        $this->insert([
            'username_or_email' => $login,
            'ip' => $ip,
            'success' => $success ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function recentFailures($loginOrIp, $minutes = 15)
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        return $this->where("(username_or_email = '{$loginOrIp}' OR ip = '{$loginOrIp}')")
                    ->where('success', 0)
                    ->where('created_at >=', $since)
                    ->countAllResults();
    }
}

<?php
/**
 * CookieManager - Centralized cookie management utility
 * Eliminates cookie code duplication across the application
 */
class CookieManager
{
    private static $rememberCookieNames = [
        'remember_user_id',
        'remember_password',
        'remember_user_type',
        'remember_user_name'
    ];

    /**
     * Set remember me cookies for user login
     */
    public static function setRememberMeCookies($userInfo, $expireDays = 30)
    {
        $expireTime = time() + ($expireDays * 24 * 60 * 60);

        $cookies = [
            'remember_user_id' => $userInfo['id'],
            'remember_password' => $userInfo['password'],
            'remember_user_type' => $userInfo['type'],
            'remember_user_name' => $userInfo['name']
        ];

        foreach ($cookies as $name => $value) {
            setcookie($name, $value, $expireTime, '/', '', false, true);
        }
    }

    /**
     * Clear all remember me cookies
     */
    public static function clearRememberMeCookies()
    {
        foreach (self::$rememberCookieNames as $cookieName) {
            if (isset($_COOKIE[$cookieName])) {
                setcookie($cookieName, '', time() - 3600, '/', '', false, true);
            }
        }
    }

    /**
     * Check if remember me cookies exist
     */
    public static function hasRememberMeCookies()
    {
        return isset($_COOKIE['remember_user_id']) &&
            isset($_COOKIE['remember_password']) &&
            isset($_COOKIE['remember_user_type']);
    }

    /**
     * Get remember me cookie data
     */
    public static function getRememberMeData()
    {
        if (!self::hasRememberMeCookies()) {
            return null;
        }

        return [
            'user_id' => $_COOKIE['remember_user_id'],
            'password' => $_COOKIE['remember_password'],
            'user_type' => $_COOKIE['remember_user_type'],
            'user_name' => $_COOKIE['remember_user_name'] ?? ''
        ];
    }


}
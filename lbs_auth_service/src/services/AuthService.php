<?php

namespace lbs\auth\services;



use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
  public function signin($connection ,$users): array|string
  {
    try {
      $users=base64_decode($users);
      $users=explode(":",$users);
      $username=$users[0];
      $password=$users[1];
      $user = $connection->users->authLbs->findOne(['usermail' => $username]);
      if ($user == null) {
        throw new Exception("User not found", 404);
      }
      if (!password_verify($password, $user->userpswd)) {
        throw new Exception("Wrong password", 401);
      }

      $payload = [
        'iat' => time(),
        'iss' => 'http://api.auth.local:19780',
        'exp' => time() + 3600,
        'aud' => 'http://api.auth.local:19780',
        'profil'=>[
          'name'=>$user->username,
          'mail'=>$user->usermail,
          'level'=>$user->userlevel,
        ],
      ];

      $secret = require_once __DIR__. '/../../conf/secret.php';

      $token = JWT::encode($payload, $secret, 'HS256');
      return [
        'access_token' => $token,
        'refresh_token' => $user->refresh_token,
      ];

    } catch (Exception $e) {
      return $e->getMessage();
    }
  }
  public function validate($token): array|string
  {
    try {
      $secret = require_once __DIR__. '/../../conf/secret.php';
      $decoded = JWT::decode($token, new Key($secret,'HS256'));
      return [
          "name"=>$decoded->profil->name,
          "mail"=>$decoded->profil->mail,
          "level"=>$decoded->profil->level,
      ];

    } catch (Exception $e) {
      return $e->getMessage();
    }
  }
}
<?php

namespace app\models;
use Yii;

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public static function tableName()
    {
        return 'user';
    }
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username'], 'string', 'max' => 50],
            [['firstName', 'lastName'], 'string', 'max' => 250],
            ['username', 'unique'],
            [['email', 'password', 'authKey'], 'string', 'max' => 250],
        ];
    }
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'username' => 'Username',
            'password' => 'Password',
            'authKey' => 'Auth Key',
            'accessToken' => 'Access Token',
        ];
    }
    public function generateAuthKey()
    {
        $this->authKey = Yii::$app->security->generateRandomString();
    }

    private function getUniqueAccessToken() {
        $resultado = md5(Yii::$app->security->generateRandomString() . '_' . time());
        $identity = $this->findIdentityByAccessToken($resultado);
        if ($identity) {
            $resultado = $this->getUniqueAccessToken();
        }
        return $resultado;
    }

    public static function findIdentity($id){
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null){
        return static::findOne(['accessToken' => $token]);
    }

    public function beforeSave($insert) {
        if ($this->isNewRecord) {
            $this->accessToken = $this->getUniqueAccessToken();
        }
        return parent::beforeSave($insert);
    }

    public function validateAccessToken($access_token){
        return $this->accessToken === $accessToken;
    }

    public function getId(){
        return $this->id;
    }

    public function isAdmin(){
        return $this->isAdmin == 1;
    }

    public function getAuthKey(){
        return $this->authKey;
    }
    
    public function validateAuthKey($authKey){
        return $this->authKey === $authKey;
    }
    
    public static function findByUsername($username){
        return self::findOne(['username'=>$username]);
    }
    
    public static function findById($id){
        return self::findOne(['id'=>$id]);
    }
    
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    public function setPassword($password){
        $this->password = Yii::$app->getSecurity()->generatePasswordHash($password);
    }

}

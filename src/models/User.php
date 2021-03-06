<?php

class User extends Model {
    protected static $tableName = 'users';
    protected static $columns = ['id','name','birth', 'series', 'sex','password','email','photo', 'qtdAccess', 'registrationDate', 'lastAcess','active','is_admin'];    
    
    public function insert(){
        $this->validateRegister();
        $this->active = $this->active ? 0 : 1;
        $this->is_admin = $this->is_admin ? 1 : 0;
        $this->birth = $this->getFormattedDate();
        switch($this->sex){
            case "F": 
                $this->photo = "default_female.png";
                break;
            case "M":
                $this->photo = "default_male.png";
                break;
            case "O":
                $this->photo = null;
                break;
            default:
                $this->photo = null;
        }
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        parent:: insert();
    }

    public function updateEmail(){
        $this->validateEmail();
        return parent:: update();
    }
    
    public function updatePassword(){
        $this->validatePassword();
        $this->password = password_hash($this->newPassword, PASSWORD_DEFAULT);
        return parent:: update();
    }

    public function updateEditProfile(){
        $this->validateEditProfile();
        $this->birth = $this->getFormattedDate();
        return parent:: update();
    }

    public function validatePassword(){
        $errors = [];

        if(!$this->currentPassword){
            $errors['currentPassword'] = "Digite a sua senha atual";
        }

        if(!$this->newPassword){
            $errors['newPassword'] = "O campo nova senha é obrigatório";
        }else if(strlen($this->newPassword) > 18){
            $errors['newPassword'] = "A senha não pode ser muito grande!";
        }

        if(!$this->passwordConfirmation){
            $errors['passwordConfirmation'] = "A confirmação de senha é obrigatória";
        }

        if(count($errors) > 0){
            throw new AppArrayException($errors);
        }

        if($this->newPassword !== $this->passwordConfirmation){
            $errors['newPassword'] = "As senhas não se coincidem";
            $errors['passwordConfirmation'] = "As senhas não se coincidem";
        }

        if(count($errors) > 0){
            throw new AppArrayException($errors);
        }
        
        $user = User::getOne(['id' => $this->id], 'password');
        if(!password_verify($this->currentPassword, $user->password)){
            $errors['currentPassword'] = "A senha está incorreta!";
        }

        if(count($errors) > 0){
            throw new AppArrayException($errors);
        }
    }

    public function validateEmail(){
        $errors = [];
        
        if(!$this->email){
            $errors['email'] = "O campo e-mail é obrigatório!";            
        }else if(strlen($this->email) > 100){   
            $errors['email'] = "O E-mail só pode ter 100 caracteres";            
        }

        if(count($errors) > 0){
            throw new AppArrayException($errors);
        }
        
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            $errors['email'] = "Digite o e-mail corretamente!";
        }

        if(count($errors) > 0){
            throw new AppArrayException($errors);
        }

        if((Database::getResultFromQuery("SELECT email FROM users WHERE email = '$this->email'"))->num_rows != 0){
            $errors['email'] = "Este email já está cadastrado!";
        }

        if(count($errors) > 0){
            throw new AppArrayException($errors);
        }
    }

    public function validateEditProfile(){
        $errors = [];

        if(!$this->name){
            $errors['name'] = "O campo nome é obrigatório!";
        }else if(strlen($this->name) > 100){
            $errors['name'] = "O nome só pode ter 100 caracteres";
        }
        
        if(!$this->day || !$this->month || !$this->year){
            $errors['birth'] = "Preencha todos os campos";
        }

        if(!$this->series || $this->series === ''){
            $errors['series'] = "O campo série é obrigatório";
        }

        if(!$this->sex){
            $errors['sex'] = "O campo sexo é obrigatório";
        }

        if(!$this->dateValidate()){
            $errors['birth'] = "Digite a data corretamente";
        }

        if(!$this->seriesValidate()){
            $errors['series'] = "Preencha o campo corretamente";
        }
        
        if(!$this->sexValidate()){
            $errors['sex'] = "Preencha o campo corretamente";
        }

        if(count($errors) > 0){
            throw new AppArrayException($errors);
        }
    }

    public function validateRegister() {
        $errors = [];

        if(!$this->name){
            $errors['name'] = "O campo nome é obrigatório!";
        }else if(strlen($this->name) > 100){
            $errors['name'] = "O nome só pode ter 100 caracteres";
        }

        if(!$this->email){
            $errors['email'] = "O campo e-mail é obrigatório!";            
        }else if(strlen($this->email) > 100){   
            $errors['email'] = "O E-mail só pode ter 100 caracteres";            
        }

        if(!$this->day || !$this->month || !$this->year){
            $errors['birth'] = "Preencha todos os campos";
        }
        
        if(!$this->series || $this->series === ''){
            $errors['series'] = "O campo série é obrigatório";
        }
        
        if(!$this->sex){
            $errors['sex'] = "O campo sexo é obrigatório";
        }
        
        if(!$this->password){
            $errors['password'] = "O campo senha é obrigatório";
        }else if(strlen($this->password) > 18){
            $errors['password'] = "A senha não pode ser muito grande!";
        }
        
        if(!$this->passwordConfirmation){
            $errors['passwordConfirmation'] = "A confirmação de senha é obrigatória";
        }        
        
        if(count($errors) > 0){
            throw new AppArrayException($errors);
        }
        
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            $errors['email'] = "Digite o e-mail corretamente!";
        }
                
        if(!$this->dateValidate()){
            $errors['birth'] = "Digite a data corretamente";
        }
        
        if(!$this->seriesValidate()){
            $errors['series'] = "Preencha o campo corretamente";
        }
        
        if(!$this->sexValidate()){
            $errors['sex'] = "Preencha o campo corretamente";
        }
        
        if($this->password !== $this->passwordConfirmation){
            $errors['password'] = "As senhas não se coincidem";
            $errors['passwordConfirmation'] =  "As senhas não se coincidem";
        }
        
        if(count($errors) > 0){
            throw new AppArrayException($errors);
        }
        
        if((Database::getResultFromQuery("SELECT email FROM users WHERE email = '$this->email'"))->num_rows != 0){
            $errors['email'] = "Este email já está cadastrado!";
        }

        if(count($errors) > 0){
            throw new AppArrayException($errors);
        }
    }

    private function seriesValidate(){
        if($this->series){
            $sel = [1,2,3];
            $sum = 0;
            foreach($sel as $value){
                if($this->series == $value){
                    $sum++;
                }
            }
            if($sum !== 1){
                return false;
            }
        }
        return true;    
    }

    private function sexValidate(){
        if($this->sex){
            $sel = ['F','M','O'];
            $sum = 0;
            foreach($sel as $value){
                if($this->sex == $value){
                    $sum++;
                }
            }
            if($sum !== 1){
                return false;
            }
        }
        return true;
    }
    
    private function dateValidate(){
        if($this->day < 1 || $this->day > 31){
            return false;
        }
        
        if($this->month < 1 || $this->month > 12){
            return false;
        }
        
        if($this->year < (intval(date('Y')) - 51) || $this->year > intval(date('Y'))){
            return false;
        }

        $date = new DateTime($this->getFormattedDate());
        if($this->getFormattedDate() !== date("Y-m-d", $date->getTimestamp())){
            return false;
        }
        
        return true;
    }

    private function getFormattedDate(){
        return "{$this->year}-{$this->month}-{$this->day}";
    }

    public function is_admin(){
        return parent::getOne(['email' => $this->email, 'id' => $this->id], 'is_admin');
    }
}
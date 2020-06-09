<?php
require_once("bin/conekta-php-master/lib/Conekta.php");
class Payment{

    private $Apikey="key_Ax9YM8UMUQQUxHAeehKv9g";
    private $ApiVersion="2.0.0";

    private $UserDB="root";
    private $PassDB="";
    private $ServerDB="localhost";
    private $DataBaseDB="pagocurso";

    public function __construct($token,$card,$name,$description,$total,$email){
        //tengo los atributos en contructor
        $this->token=$token;
        $this->card=$card;
        $this->name=$name;
        $this->description=$description;
        $this->total=$total;
        $this->email=$email;
    }
    public function Pay(){        
    
    \Conekta\Conekta::setApiKey($this->Apikey);
    \Conekta\Conekta::setApiVersion($this->ApiVersion);
    if(!$this->Validate())    
        return false;
    if(!$this->CreateCustomer())
        return false;
    if(!$this->CreateOrder())
        return false;
        $this->Save();


     return true; 
    }
    public function Save(){
           $link = new PDO("mysql:host=".$this->ServerDB.";dbname=".$this->DataBaseDB, $this->UserDB, $this->PassDB);
      
            $statement = $link->prepare("INSERT INTO payment (total,date_created,description,name,number_card,email,order_id)
                VALUES (:total, now(), :description,:name,:number_card,:email,:order_id)");
      
            $statement->execute([
                'total' => $this->total,
                'description' => $this->description,
                'name' => utf8_decode($this->name),
                'number_card'=> substr($this->card,strlen($this->card)-5,4),
                 'email'=>$this->email,
                'order_id'=>$this->order->id
             ]);
            
            $this->order_number = $link->lastInsertId();
          
           }
    public function CreateOrder(){
        try {
            //code...
            $this->order=\Conekta\Order::create(
                array(
                    "amount"=>$this-total,
                    "line_items"=>array(
                        array(
                            "name"=>$this->descripcion,
                            "unit_price"=>$this->total*100,//se multiplica  100 por conekta
                            "quantity"=>1
                        )
                        ),
                        "currency"=>"MXN",
                        "customer_info"=>array(
                            "customer_id"=>$this->customer->id
                        ),
                        "charges"=>array(
                            array(
                                "payment_method"=>array(
                                    "type"=>"default"
                                )
                            )
                        )                        

                )

            );
                
        }catch (\Conekta\ProccessingError $error) {
            $this->error=$error->getMessage();
            return false; 
            //throw $th;
        }catch(\Conekta\ParameterValidationError $error){
            $this->error=$error->getMessage();
            return false; 

        }catch(\Conekta\Handler $error){
            $this->error=$error->getMessage();
            return false; 
        }
        return true;
    }

    
    public function CreateCustomer(){
        try {
            // creo un cliente
            $this->customer =\Conekta\Customer::create(
                array(
                    "name"=>$this->name,
                    "email"=>$this->email,
                    //telefono
                    "payment_sources"=>array(
                        array(
                            "type"=>card,
                            "token_id"=>$this->token
                        )
                    )
                )

            );
        } catch (\Conekta\ProccessingError $error) {
            $this->error=$error->getMessage();
            return false;
        }catch (\Conekta\ParameterValidationError $error) {
            $this->error=$error->getMessage();
            return false;
        }catch(\Conekta\Handler $error){
            $this->error=$error->getMessage();
            return false;
        }
        return true;
    }

    //vamos a separar los como se raliza los pagos en metodos
    public function Validate(){
        if($this->card=="" || $this->name=="" || $this->description || $this->total || $this->email){
            $this->error='El numero de tarjeta, el nombre, concepto, monto, el correo electronico son obligatorio';
            return false;
        }
        if(strlen($this->card)<=14){
            $this->error='El numero de la tarjeta debe contener al menos 16 caracteres';
            return false;
        }
        if(!filter_var($this->email,FILTER_VALIDATE_EMAIL)){
            $this->error='El correo electronica no tiene un formato de un correo valido';
            return false;
        }
        if($this->total<=20){
            $this->error='El monto debe ser mayor a 20 pesos';
            return false;
        }
        return true;
    }
}

?>
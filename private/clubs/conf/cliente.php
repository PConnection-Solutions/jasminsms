<?php
class cliente
{
    private $conn;
    private $msisdn;
    private $id_clubs;
    public $new_user;
    public $cliente_data;
    public $cliente_registrado;
    
    public function __construct($conn,$msisdn,$id_clubs)
    {
        $this->conn = $conn;
        $this->msisdn = $msisdn;
        $this->id_clubs = $id_clubs;
        $this->new_user = true;
        $this->cliente_registrado = false;
         
        $this->cliente_data = $this->load_data_user();
        if (!$this->cliente_data)
        {
            $this->cliente_registrado = false;
        }
        else
        {
            $this->cliente_registrado = true;
        }
        
    }
    private function load_data_user()
    {
        $msisdn = $this->msisdn;
        $id_clubs = $this->id_clubs;
        $sql = "select * from clubs_MSISDN where msisdn = '$msisdn' and id_clubs = $id_clubs";
        $response = mysql_query($sql,$this->conn); 
        if (mysql_num_rows($response))
        {
            $this->new_user = false;
            return mysql_fetch_array($response);
        }
        else
        {
            return false;
        }  
    }
    private function insert_user()
    {
        $msisdn = $this->msisdn;
        $id_clubs = $this->id_clubs;
        $sql = "insert  into clubs_MSISDN (id_clubs,msisdn,fecha_alta,fecha_ult_evento,state,fecha_cobro)". 
                " values ($id_clubs,'$msisdn',DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')),DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')),'enable',DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')))" ;
        $response = mysql_query($sql,$this->conn);
        if ($response)
        {
            $this->new_user = true;
            return mysql_insert_id($this->conn);
        }
        else
        {
            return false;
        } 
    }
    public function disable_user()
    {
        $id_user = $this->cliente_data['id_msisdn'];
        $sql ="update clubs_MSISDN set state = 'disable',fecha_baja=DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')) where id_msisdn =$id_user";
        $response = mysql_query($sql,$this->conn);
        $this->cliente_data = $this->load_data_user_id($id_user);
        
        return $response;
        
    }
    public function enable_user()
    {
        $id_user = $this->cliente_data['id_msisdn'];
        $sql ="update clubs_MSISDN set state = 'enable',fecha_alta=DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')),fecha_ult_evento=DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')) where id_msisdn =$id_user";
        $response = mysql_query($sql,$this->conn);
        $this->cliente_data = $this->load_data_user_id($id_user);
        
        return $response;
        
    }
    private function load_data_user_id($id)
    {
        
        $sql = "select * from clubs_MSISDN where id_msisdn = $id";
        $response = mysql_query($sql,$this->conn); 
        if (mysql_num_rows($response))
        {
             return mysql_fetch_array($response);
        }
        else
        {
            return false;
        }  
    }
    public function refresh_data()
    {
        $id_user = $this->cliente_data['id_msisdn'];
        $this->cliente_data = $this->load_data_user_id($id_user);
    }
    public function insertar_nuevo_usuario()
    {
        if ($this->cliente_registrado === false)
        {
            $id_user = $this->insert_user();
            $this->cliente_data = $this->load_data_user_id($id_user);
        }
    }
    public function disable_user_id($id_user)
    {
        $sql ="update clubs_MSISDN set state = 'disable',fecha_baja=DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')) where id_msisdn =$id_user";
        $response = mysql_query($sql,$this->conn);
        
    }
    public function enable_user_id($id_user)
    {
        $sql ="update clubs_MSISDN set state = 'enable',fecha_alta=DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')),fecha_ult_evento=DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')) where id_msisdn =$id_user";
        $response = mysql_query($sql,$this->conn);
        
    }
    public function update_question_points($id_user,$next_points,$id_question)
    {
        $sql = "update clubs_MSISDN set next_points = $next_points, id_question=$id_question".
                " where id_msisdn =$id_user";
        return mysql_query($sql,$this->conn);
    }
}       
?>

<?php
class trivia
{
    private $conn;
    private $positive,$negative;
    private $id_club;

    public function __construct($conn,$id_club)
    {
        $this->conn = $conn;
        $this->positive = array();
        $this->negative = array();
        $this->id_club = $id_club;
        $this->iniciar_mensajes_prefijo($this->id_club);
    }
    public function return_random_question()
    {
        $id_club = $this->id_club;
        $sql = "select * from clubs_TRIVIA_DET where id_club =$id_club order by rand()";
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
    public function check_answer($word_response,$id_trivia)
    {
        /*
        Regresa true o false en base a la respuesta que dio el usuario
        */
        $sql = "select * from clubs_TRIVIA_DET where id_trivia = $id_trivia";
        $response = mysql_query($sql,$this->conn);
        if (mysql_num_rows($response))
        {
            $response = mysql_fetch_array($response);
            $answer = strtoupper($response['answer']);
            $distancia = levenshtein($answer,strtoupper($word_response));
            if (($answer == strtoupper($word_response)) || $distancia == 1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
    }
    private function iniciar_mensajes_prefijo($id_club)
    {
        $sql = "select * from clubs_TRIVIA_PREFIX where id_club = $id_club";
        $response = mysql_query($sql,$this->conn);
        while($row = mysql_fetch_array($response))
        {
            $this->positive[] = $row['positive_response'];
            $this->negative[] = $row['negative_response'];
        }
        
    }
    public function random_prefijo($message_response=true)
    {
        if ($message_response)
        {
            return $this->positive[array_rand($this->positive)];
            
            
        }
        else
        {
            return $this->negative[array_rand($this->negative)];
            
        }
    }
}
  
?>

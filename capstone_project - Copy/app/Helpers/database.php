<?php
    $servername = "localhost"; //servername
    $username="root"; //serverusername
    $password="";
    $db_name = "capstone_db"; //name of the used database
    $conn = new mysqli($servername, $username, $password, $db_name, 3306);
    if ($conn->connect_error){
        die("Connection failed".$conn->connect_error);
    }
    
    /*else{
        $sql = "insert into users(username, password, emp_position) values('doe', '1234', 'Administrator' )";
        $result = mysqli_query($conn,$sql);
        if(mysqli_query($conn, $sql))
    echo "Records Successfully save";
    
    else
    {
        echo "Cannot save to database".$sql."<br/>".mysqli_error($conn);
    }*/
        
    
?>
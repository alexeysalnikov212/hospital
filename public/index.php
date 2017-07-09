<?php
    // configuration
    require("../includes/config.php"); 

 if ($_SERVER["REQUEST_METHOD"] == "GET")
    {
        $datasets=[];
        //если на главную зашли незалогинившись, значит відаем форму заказа талона
        if (empty($_SESSION["user_id"]))
        {
        $firms = CS50::query("SELECT firms.id, CONCAT(firms.name, ' Адрес: ', CONCAT_WS(', ', firms.postindex, cities.name, firms.street, firms.house)) as name FROM firms, cities where firms.city_id=cities.id order by 2");
        $datasets["firms"] = $firms;
        //форме передается массив firms, содержащий id и name
        render("getticket.php", ["title" => "Получить талон", "datasets" => $datasets]);
        }
    }    
 else if ($_SERVER["REQUEST_METHOD"] == "POST")
 {
       //если пост-запрос отправил неавторизованный пользователь, то обрабатываем его с помощью формы getticket.php
     if (empty($_SESSION["user_id"]))
     {
        $postitions=[];
        $datasets=[];
        $firms = CS50::query("SELECT firms.id, CONCAT (firms.name, ' Адрес: ', CONCAT_WS(', ', firms.postindex, cities.name, firms.street, firms.house)) as name FROM firms, cities where firms.city_id=cities.id order by 2");
        $datasets["firms"] = $firms;
        $positions["firms"] =  $_POST["firm"];
        if (!empty($_POST["firm"]) && isset($datasets["firms"]) && $datasets["firms"])
        {
            $departments = CS50::query("SELECT departments.id, departments.name FROM firms, departments where departments.firm_id=firms.id and firms.id = ? order by 2", $_POST["firm"]);
            $datasets["departments"] = $departments;
        }

        if (!empty($_POST["department"]) && isset($datasets["departments"]) && $datasets["departments"])
        {
            $workplaces = CS50::query("SELECT workplaces.id, CONCAT(empl_surname, ' ', empl_name, ' ', empl_lastname, '. ', workplaces.name) as name FROM departments,workplaces where workplaces.department_id=departments.id and departments.id = ? order by 2", $_POST["department"]);
            $positions["departments"] =  $_POST["department"];
            $datasets["workplaces"] = $workplaces;
        }
        if (!empty($_POST["workplace"]) && isset($datasets["workplaces"]) && $datasets["workplaces"])
        {
            if (!empty($_POST["appointmentdate"]))
            {
                $cur_date = $_POST["appointmentdate"];
            }
            else
            {
                $cur_date = date('d.m.Y');
            }
            $appointments = CS50::query("SELECT queues.id, services.name AS servicename, CONCAT( TIME( queues.time_begin ), '-', TIME( queues.time_begin + INTERVAL services.duration "
    . "MINUTE ) ) "
    . "FROM queues, schedule, services, workplaces "
    . "WHERE queues.schedule_id = schedule.id "
    . "AND schedule.service_id = services.id "
    . "AND workplaces.id = schedule.worplace_id "
    . "AND workplaces.id = ? "
    . "AND schedule.week_day = WEEKDAY( ? )", $_POST["workplace"], $cur_date);
            $positions["workplaces"] =  $_POST["workplace"];
            $positions["cur_date"] = $cur_date;
        }
        //форме передается массив массивов данных для заполнения данными элементов формы, а также массив выбранных значений для каждого элемента (если они есть)
        render("getticket.php", ["title" => "Запись на приём", "positions" => $positions, "datasets" => $datasets]);
     }
 }



?>

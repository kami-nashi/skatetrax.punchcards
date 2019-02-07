<?php

################################################################################
# import the db stuff, get the sql string, return the data
################################################################################
function db_stuff($sql){
  require 'db_connect.php';
  $result = mysql_query($sql, $link);
  if (!$result) {
    echo "DB Error, could not query the database\n";
    echo 'MySQL Error: ' . mysql_error();
    exit;
  }

  return $result;
  }

################################################################################
# almost every time we get ice time, we need to convert the minutes to hours
################################################################################
function basicmath($icem){
	$hours = $icem / 60;
	return $hours;
  }

################################################################################
# logic for building out punch card data for multiple location
################################################################################
function punches_tables(){
$sql = 'SELECT * FROM ice_punch, locations WHERE ice_punch.punch_location = locations.id AND ice_punch.id IN (SELECT MAX(id) FROM ice_punch GROUP BY punch_location)';
$result = db_stuff($sql);

      while ($row = mysql_fetch_assoc($result)) {
        echo "<p><h1>".$row['location_id']." ".$row['location_city']." ".$row['location_state']." "." </h1><p/>   ";
        $sql2 = "SELECT * FROM ice_punch, locations WHERE ice_punch.punch_location = ".$row['punch_location']." AND locations.id = ".$row['punch_location'];
        $sql3 = "SELECT DISTINCT ice_time.date, ice_time.ice_time, ice_time.skate_type, ice_time.rink_id, ice_punch.punch_time, ice_punch.punch_cost, ice_punch.punch_location, locations.* FROM ice_time, ice_punch, locations WHERE ice_time.skate_type = 8 AND ice_time.rink_id = ".$row['punch_location']." AND ice_punch.punch_location = ".$row['punch_location']." AND locations.id = ".$row['punch_location'];
        $result2 = db_stuff($sql2);
        $result3 = db_stuff($sql3); # Get raw total of minutes skated at rinks on punch where the rink ID and the card ID match
        $sum_punchtime = 0;
        $sum_punchcost = 0;
        while ($rowa = mysql_fetch_assoc($result2)) {
           $sum_punchtime += $row['punch_time'];
           $punch_hours = $sum_punchtime / 60;
           $sum_punchcost += $row['punch_cost'];
           #echo "<p><h2>".$rowa['location_id']." ".$rowa['location_city']." ".$rowa['location_state']." " .$punch_hours." 2</h2><p/>";
        }
        $skate_total = 0;
        $punch_total = 0;
	$punches_total = 0;

        while ($rowb = mysql_fetch_assoc($result3)) {
           
           $skate_total += $rowb['ice_time'];
           $punch_down = $rowb['ice_time'] / 60;
           $punches_total += $punch_down;
        }
        #echo "Raw Minutes - Skated and Punched: ".$skate_total."<br>";
        #echo "Raw Minutes - Total on All Cards: ".$sum_punchtime."<br>";
        echo "Hours On All Cards....... ".$punch_hours."<br>";
        echo "Hours Skated Punched... ".$punches_total."<br>";
        $diff = $punch_hours - $punches_total;
        echo "Hours Remaining............".$diff;
   }
}

?>
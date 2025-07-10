<div class="contat">

    <div class="more">
    
        <div class="more-info">
            <p style="font-size: 35px; color: white; margin-bottom: 40px; ">For more info contact:</p>
        
           <div class="sup-contain">

               <div class="contain">
                       <?php
                           $host = "localhost";
                           $user = "root";
                           $password = "";
                           $database = "cybersite";
                           
                           //Create connection
                           $connection = new mysqli($host, $user, $password, $database);
       
                           //Check connection
                           if($connection->connect_error) {
                               die("Connection Failed:" . $connection->connect_error);
                           }
       
                           //read all row from database table
                           $sql = "SELECT * FROM users";
                           $result = $connection->query($sql);
       
                           if (!$result) {
                               die("Invaild query" . $connection->error);
                           }
                           //read data of each row
                           while($row = $result->fetch_assoc()) {
                            $sql2 = mysqli_query($conn, "SELECT  `postiton`FROM`position` WHERE `position`.`unique_id` = $row[position]");

              $ro = mysqli_fetch_assoc($sql2);
                               if($row['position'] == '7'){
       
                                   echo"
                                        <p style='text-transform:uppercase; text-align: center; font-weight: 700; font-size: 25px; letter-spacing: 1px; font-family: monospace;'>$ro[postiton]</p>
                                       <img src='../php/images/$row[img]' alt=''>
                                       <h3>Username: $row[username]</h3>
                                       <p>Email: $row[email]</p>
                                       <p>Phone number: $row[phone]</p>
                                   ";
                               }
                           }
                       ?>
               </div>
               <div class="contain">
                       <?php
                           $host = "localhost";
                           $user = "root";
                           $password = "";
                           $database = "cybersite";
                           
                           //Create connection
                           $connection = new mysqli($host, $user, $password, $database);
       
                           //Check connection
                           if($connection->connect_error) {
                               die("Connection Failed:" . $connection->connect_error);
                           }
       
                           //read all row from database table
                           $sql = "SELECT * FROM users";
                           $result = $connection->query($sql);
       
                           if (!$result) {
                               die("Invaild query" . $connection->error);
                           }
                           //read data of each row
                           while($row = $result->fetch_assoc()) {
                            $sql2 = mysqli_query($conn, "SELECT  `postiton`FROM`position` WHERE `position`.`unique_id` = $row[position]");

              $ro = mysqli_fetch_assoc($sql2);
                               if($row['position'] == '9'){
       
                                   echo"
                                        <p style='text-transform:uppercase; text-align: center; font-weight: 700; font-size: 25px; letter-spacing: 1px; font-family: monospace;'>$ro[postiton]</p>
                                       <img src='../php/images/$row[img]' alt=''>
                                       <h3>Username: $row[username]</h3>
                                       <p>Email: $row[email]</p>
                                       <p>Phone number: $row[phone]</p>
                                   ";
                               }
                           }
                       ?>
               </div>
               <div class="contain">
                       <?php
                           $host = "localhost";
                           $user = "root";
                           $password = "";
                           $database = "cybersite";
                           
                           //Create connection
                           $connection = new mysqli($host, $user, $password, $database);
       
                           //Check connection
                           if($connection->connect_error) {
                               die("Connection Failed:" . $connection->connect_error);
                           }
       
                           //read all row from database table
                           $sql = "SELECT * FROM users";
                           $result = $connection->query($sql);
       
                           if (!$result) {
                               die("Invaild query" . $connection->error);
                           }
                           //read data of each row
                           while($row = $result->fetch_assoc()) {
                               $sql2 = mysqli_query($conn, "SELECT  `postiton`FROM`position` WHERE `position`.`unique_id` = $row[position]");

                                $ro = mysqli_fetch_assoc($sql2);
                               if($row['position'] == '12'){
       
                                   echo"
                                        <p style='text-transform:uppercase; text-align: center; font-weight: 700; font-size: 25px; letter-spacing: 1px; font-family: monospace;'>$ro[postiton]</p>
                                       <img src='../php/images/$row[img]' alt=''>
                                       <h3>Username: $row[username]</h3>
                                       <p>Email: $row[email]</p>
                                       <p>Phone number: $row[phone]</p>
                                   ";
                               }
                           }
                       ?>
               </div>
               <div class="contain">
                       <?php
                           $host = "localhost";
                           $user = "root";
                           $password = "";
                           $database = "cybersite";
                           
                           //Create connection
                           $connection = new mysqli($host, $user, $password, $database);
       
                           //Check connection
                           if($connection->connect_error) {
                               die("Connection Failed:" . $connection->connect_error);
                           }
       
                           //read all row from database table
                           $sql = "SELECT * FROM users";
                           $result = $connection->query($sql);
       
                           if (!$result) {
                               die("Invaild query" . $connection->error);
                           }
                           //read data of each row
                           while($row = $result->fetch_assoc()) {
                               $sql2 = mysqli_query($conn, "SELECT  `postiton`FROM`position` WHERE `position`.`unique_id` = $row[position]");

                                $ro = mysqli_fetch_assoc($sql2);
                               if($row['position'] == '11'){
       
                                   echo"
                                        <p style='text-transform:uppercase; text-align: center; font-weight: 700; font-size: 25px; letter-spacing: 1px; font-family: monospace;'>$ro[postiton]</p>
                                       <img src='../php/images/$row[img]' alt=''>
                                       <h3>Username: $row[username]</h3>
                                       <p>Email: $row[email]</p>
                                       <p>Phone number: $row[phone]</p>
                                   ";
                               }
                           }
                       ?>
               </div>
           </div>
        </div>

    
        <div class="more-info">
            <p style="font-size: 35px; color: white; margin-bottom: 40px; ">Or</p>
        
           <div class="su-contain">

               <div class="contain">
                       <?php
                           $host = "localhost";
                           $user = "root";
                           $password = "";
                           $database = "cybersite";
                           
                           //Create connection
                           $connection = new mysqli($host, $user, $password, $database);
       
                           //Check connection
                           if($connection->connect_error) {
                               die("Connection Failed:" . $connection->connect_error);
                           }
       
                           //read all row from database table
                           $sql = "SELECT * FROM users";
                           $result = $connection->query($sql);
       
                           if (!$result) {
                               die("Invaild query" . $connection->error);
                           }
                           //read data of each row
                           while($row = $result->fetch_assoc()) {
                            $sql2 = mysqli_query($conn, "SELECT  `postiton`FROM`position` WHERE `position`.`unique_id` = $row[position]");

              $ro = mysqli_fetch_assoc($sql2);
                               if($row['position'] == '1'){
       
                                   echo"
                                        <p style='text-transform:uppercase; text-align: center; font-weight: 700; font-size: 25px; letter-spacing: 1px; font-family: monospace;'>$ro[postiton]</p>
                                       <img src='../php/images/$row[img]' alt=''>
                                       <h3>Username: $row[username]</h3>
                                       <p>Email: $row[email]</p>
                                       <p>Phone number: $row[phone]</p>
                                   ";
                               }
                           }
                       ?>
               </div>
               <div class="contain">
                       <?php
                           $host = "localhost";
                           $user = "root";
                           $password = "";
                           $database = "cybersite";
                           
                           //Create connection
                           $connection = new mysqli($host, $user, $password, $database);
       
                           //Check connection
                           if($connection->connect_error) {
                               die("Connection Failed:" . $connection->connect_error);
                           }
       
                           //read all row from database table
                           $sql = "SELECT * FROM users";
                           $result = $connection->query($sql);
       
                           if (!$result) {
                               die("Invaild query" . $connection->error);
                           }
                           //read data of each row
                           while($row = $result->fetch_assoc()) {
                            $sql2 = mysqli_query($conn, "SELECT  `postiton`FROM`position` WHERE `position`.`unique_id` = $row[position]");

              $ro = mysqli_fetch_assoc($sql2);
                               if($row['position'] == '2'){
       
                                   echo"
                                        <p style='text-transform:uppercase; text-align: center; font-weight: 700; font-size: 25px; letter-spacing: 1px; font-family: monospace;'>$ro[postiton]</p>
                                       <img src='../php/images/$row[img]' alt=''>
                                       <h3>Username: $row[username]</h3>
                                       <p>Email: $row[email]</p>
                                       <p>Phone number: $row[phone]</p>
                                   ";
                               }
                           }
                       ?>
               </div>
               <div class="contain">
                       <?php
                           $host = "localhost";
                           $user = "root";
                           $password = "";
                           $database = "cybersite";
                           
                           //Create connection
                           $connection = new mysqli($host, $user, $password, $database);
       
                           //Check connection
                           if($connection->connect_error) {
                               die("Connection Failed:" . $connection->connect_error);
                           }
       
                           //read all row from database table
                           $sql = "SELECT * FROM users";
                           $result = $connection->query($sql);
       
                           if (!$result) {
                               die("Invaild query" . $connection->error);
                           }
                           //read data of each row
                           while($row = $result->fetch_assoc()) {
                            $sql2 = mysqli_query($conn, "SELECT  `postiton`FROM`position` WHERE `position`.`unique_id` = $row[position]");

              $ro = mysqli_fetch_assoc($sql2);
                               if($row['position'] == '3'){
       
                                   echo"
                                        <p style='text-transform:uppercase; text-align: center; font-weight: 700; font-size: 25px; letter-spacing: 1px; font-family: monospace;'>$ro[postiton]</p>
                                       <img src='../php/images/$row[img]' alt=''>
                                       <h3>Username: $row[username]</h3>
                                       <p>Email: $row[email]</p>
                                       <p>Phone number: $row[phone]</p>
                                   ";
                               }
                           }
                       ?>
               </div>
               <div class="contain">
                       <?php
                           $host = "localhost";
                           $user = "root";
                           $password = "";
                           $database = "cybersite";
                           
                           //Create connection
                           $connection = new mysqli($host, $user, $password, $database);
       
                           //Check connection
                           if($connection->connect_error) {
                               die("Connection Failed:" . $connection->connect_error);
                           }
       
                           //read all row from database table
                           $sql = "SELECT * FROM users";
                           $result = $connection->query($sql);
       
                           if (!$result) {
                               die("Invaild query" . $connection->error);
                           }
                           //read data of each row
                           while($row = $result->fetch_assoc()) {
                               $sql2 = mysqli_query($conn, "SELECT  `postiton`FROM`position` WHERE `position`.`unique_id` = $row[position]");

                                $ro = mysqli_fetch_assoc($sql2);
                               if($row['position'] == '15'){
       
                                   echo"
                                        <p style='text-transform:uppercase; text-align: center; font-weight: 700; font-size: 25px; letter-spacing: 1px; font-family: monospace;'>$ro[postiton]</p>
                                       <img src='../php/images/$row[img]' alt=''>
                                       <h3>Username: $row[username]</h3>
                                       <p>Email: $row[email]</p>
                                       <p>Phone number: $row[phone]</p>
                                   ";
                               }
                           }
                       ?>
               </div>
           </div>
        </div>

        
       
    </div>

</div>
                        </body>
</body>
<script src="./js/index.js"></script>
</html>
<section id="interface">
<div class="interhead">
            <div class="inter1">
                <i onclick="myBtn()" class="barrs fa fa-bars"></i>
                    <p>Dashboard/Agric</p>
            </div>



            <?php
        $find_notifications = "Select * from inf where active = 1";
        $result = mysqli_query($conn,$find_notifications);
        $count_active = '';
        $notifications_data = array(); 
        $deactive_notifications_dump = array();
         while($rows = mysqli_fetch_assoc($result)){
                 $count_active = mysqli_num_rows($result);
                 $notifications_data[] = array(
                             "n_id" => $rows['n_id'],
                             "notifications_name"=>$rows['notifications_name'],
                             "message"=>$rows['message'],
                             "date"=>$rows['date']
                 );
         }
         //only five specific posts
         $deactive_notifications = "Select * from inf where active = 0 ORDER BY n_id DESC LIMIT 0,5";
         $result = mysqli_query($conn,$deactive_notifications);
         while($rows = mysqli_fetch_assoc($result)){
           $deactive_notifications_dump[] = array(
                       "n_id" => $rows['n_id'],
                       "notifications_name"=>$rows['notifications_name'],
                       "message"=>$rows['message'],
                       "date"=>$rows['date']
           );
         }
 
      ?>
         <nav class="navbar navbar-inverse">
                 <div class="container-fluid">
                   <ul class="nav navbar-nav navbar-right">
                     <li><i class="far fa-bell"   id="over" data-value ="<?php echo $count_active;?>" style="z-index:-99 !important;font-size:27px;"></i></li>
                     <?php if(!empty($count_active)){?>
                     <div class="round" id="bell-count" data-value ="<?php echo $count_active;?>"><span><?php echo $count_active; ?></span></div>
                     <?php }?>
                      
                     <?php if(!empty($count_active)){?>
                       <div id="list">
                       <h6>New notification may come but not shown. Pls check notifications on regular bases</h6>
                        <?php
                           foreach($notifications_data as $list_rows){?>
                             <li id="message_items">
                             <div class="message alert alert-warning" data-id=<?php echo $list_rows['n_id'];?>>
                             <div class="noti">
                                <span><?php echo $list_rows['notifications_name'];?></span>
                                <span class="date"><?php echo $list_rows['date'];?></span>
                              </div>
                               <div class="msg">
                                 <p><?php 
                                   echo $list_rows['message'];
                                 ?></p>
                               </div>
                             </div>
                             </li>
                          <?php }
                        ?> 
                        </div> 
                      <?php }else{?>
                         <!--old Messages-->
                         <div id="list">
                         <h6>New notification may come but not shown. Please check notifications on regular bases</h6>
                         <?php
                           foreach($deactive_notifications_dump as $list_rows){?>
                             <li id="message_items">
                             <div class="message alert alert-danger" data-id=<?php echo $list_rows['n_id'];?>>
                              <div class="noti">
                                <span><?php echo $list_rows['notifications_name'];?></span>
                                <span class="date"><?php echo $list_rows['date'];?></span>
                              </div>
                               <div class="msg">
                                 <p><?php 
                                   echo $list_rows['message'];
                                 ?></p>
                               </div>
                             </div>
                             </li>
                          <?php }
                        ?>
                         <!--old Messages-->
                      
                      <?php } ?>
                      
                      </div>
                   </ul>
                  
                 </div>
               </nav>
        </div>

        <div class="inter-name">
            <div class="inter-name1">
                <h3 class="i-name">Agric Students</h3>
                <p>Manage the Agric students here.</p>
                <p>You can add or permanently delete members from this page.</p>
            </div>

            <div class="inter-name2">
                <div class="value">
                    <a class="box">
                        <div class="va-box">
                            <span>Total number of Agric Students</span>
                            <div class="tota">
                            <i class="fa fa-user" style="font-size: 40px;"></i>
                                <?php
                                    $query = "SELECT COUNT(*) FROM users WHERE class = 'agric'";

                                    // Execute the query
                                $result = $conn->query($query);

                                if ($result->num_rows > 0) {
                                    $ro = $result->fetch_row()[0];
                                        echo "<h3>$ro </h3>" ;
                                }
                                ?>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>



        <div class="table">
        <div class="mb-6">
          <label for="searchQuery">Search For User:</label>
          <input type="text" id="searchQuery" placeholder="Filter by username or full name...">
        </div>


        <div id="searchResults">
          <?php
          $host = "localhost";
          $user = "root";
          $password = "";
          $database = "cybersite";

          //Create connection
          $conn = new mysqli($host, $user, $password, $database);

          //Check connection
          if ($conn->connect_error) {
            die("Connection Failed:" . $conn->connect_error);
          }

          $sql = "SELECT * FROM users WHERE class='agric' ";
          $result = $conn->query($sql);

          if (!$result) {
            die("Invaild query" . $conn->error);
          }

          if (!$result) {
            die("Invaild query" . $conn->error);
          }

          if (!empty($result)) {
          ?>
                <table>
                <thead>
                <tr><th>Name</th>
            <th>Profile</th>
            <th>Username</th>
            <th>Email</th>
            <th>Year</th>
            <th>Class</th>
            <th>Room</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Position</th>
            <th>Status</th>
            <th class="edd <?= activeEdit() ?>">Edit</th>
            <th class="edd <?= activeEdit() ?>">Block</th></tr>
                </thead>
                <tbody>
                    <?php
            foreach ($result as $row) {
              $sql2 = mysqli_query($conn, "SELECT  `postiton`FROM`position` WHERE `position`.`unique_id` = $row[position]");

              $ro = mysqli_fetch_assoc($sql2);
        

             ?>
                <tr>
                    <td class='caps'><?php echo $row['fname']?></td>
                    <td><img class='pp-img' src='../php/images/<?php echo $row['img']?>'></td>
                    <td><?php echo $row['username']?></td>
                    <td><?php echo $row['email']?></td>
                    <td><?php echo $row['year']?></td>
                    <td class='caps'><?php echo $row['class']?></td>
                    <td class='caps'><?php echo $row['cnumber']?></td>
                    <td><?php echo $row['phone']?></td>
                    <td class='caps'><?php echo $row['role']?></td>
                    <td class='caps'><?php echo $ro['postiton']?></td>
                    <td class='caps'><?php echo $row['status']?></td>
                    <td class="eds <?= activeEdit() ?>">
                        <a class="ed <?= activeEdit() ?>" href="../php/useredit.php?id=<?php echo $row['user_id']?>">EDIT</a>
                    </td>
                    <td class="eds <?= activeEdit() ?>">
                        <a class='de <?= activeEdit() ?>' href='../php/delete.php?id=<?php echo $row['user_id']?>'>BLOCK</a>
                    </td>
                </tr>
            <?php
            }
            echo '</tbody>';
            echo '</table>';
          } else {
            echo '<p class="message">No users available in the database.</p>';
          }
          ?>
        </div>
      </div>
    </section>

<?php 
function activeEdit(){
    include("../php/config.php");
    $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
    if(mysqli_num_rows($sql) > 0){
      while($row = mysqli_fetch_assoc($sql)){

        return in_array($row['position'], ["1", "2", "3", "4", "5", "6", "7", "8", "13", "15"]) ? "is-all": "";
      }
    
    }
    }

?>

    
<?php include "footer.php";?>
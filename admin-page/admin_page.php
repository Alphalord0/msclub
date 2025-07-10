    <section id="interface">
        <div class="interhead">
            <div class="inter1">
                <i onclick="myBtn()" class="barrs  fa fa-bars"></i>
                    <p>Dashboard</p>
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
                     <li><i class="far fa-bell"   id="over" data-value ="<?php echo $count_active;?>" style="z-index:-99 !important;font-size:27px; cursor:pointer;"></i></li>
                     <?php if(!empty($count_active)){?>
                     <div class="round" id="bell-count" data-value ="<?php echo $count_active;?>"><span><?php echo $count_active; ?></span></div>
                     <?php }?>
                      
                     <?php if(!empty($count_active)){?>
                       <div id="list">
                       <h6>New notification may come but not shown. Please check notifications on regular bases</h6>
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
                         <h6>New notification may come but not shown. Pls check notifications on regular bases</h6>
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


        <h3 class="in-name">Welcome, <?php echo $row['username'] ?></h3>

        <div class="values">

            <a class="box" href="./?tab=members">
                <div class="val-box">
                    <span>Total Users</span>
                    <div class="total">
                    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35 " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user h-10 w-10 text-primary" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="10" r="3"></circle><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path></svg>
                        <?php
                            $query = "SELECT user_id FROM users ORDER BY user_id";
                            $query_num = mysqli_query($conn, $query);

                            $ro = mysqli_num_rows($query_num);

                            echo "<h3>$ro </h3>";
                        ?>
                    </div>
                    <p>Total number of members in the club</p>
                </div>
            </a>
            <a class="box" href="./?tab=approve">
                <div class="val-box">
                    <span>Total Pending Members</span>
                    <div class="total">
                    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-pen h-10 w-10 text-primary" aria-hidden="true"><path d="M2 21a8 8 0 0 1 10.821-7.487"></path><path d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"></path><circle cx="10" cy="8" r="5"></circle></svg>
                        <?php
                            $query = "SELECT user_id FROM requests ORDER BY user_id";
                            $query_num = mysqli_query($conn, $query);

                            $ro = mysqli_num_rows($query_num);

                            echo "<h3>$ro </h3>";
                        ?>
                    </div>
                    <p>Total number of pending members in the club</p>
                </div>
            </a>
            <a class="box" href="./?tab=event">
                <div class="val-box">
                    <span>Events Notification</span>
                    <div class="total">
                    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-clock h-10 w-10 text-primary" aria-hidden="true"><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"></path><path d="M16 2v4"></path><path d="M8 2v4"></path><path d="M3 10h5"></path><path d="M17.5 17.5 16 16.3V14"></path><circle cx="16" cy="16" r="6"></circle></svg>
                        <?php
                            $query = "SELECT n_id FROM inf WHERE active = '1' ORDER BY n_id";
                            $query_num = mysqli_query($conn, $query);

                            $ro = mysqli_num_rows($query_num);

                            echo "<h3>$ro </h3>";
                        ?>
                    </div>
                    <p>Total new event notifications in the club</p>
                </div>
            </a>
            <a class="box" href="./?tab=members">
                <div class="val-box">
                    <span>Total Revenue</span>
                    <div class="total">
                        <i class="fa fa-coins"></i>
                    <?php
                            $query = "SELECT user_id FROM users ORDER BY user_id";
                            $query_num = mysqli_query($conn, $query);

                            $ro = mysqli_num_rows($query_num);

                            echo "<h3>$ro </h3>";
                        ?>
                    </div>
                    <p>Total account of the club</p>
                </div>
            </a>

        
        </div>



                        <div class="table">
          <div class="mb-6">
            <label for="searchQuery">Search for Admin:</label>
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
            if($conn->connect_error) {
                die("Connection Failed:" . $conn->connect_error);
            }

            $sql = "SELECT * FROM users";
            $result = $conn->query($sql);

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
            <th>Gender</th>
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
                  if($row['role']=='admin'){

                ?>
                <tr>
                    <td class='caps'><?php echo $row['fname']?></td>
                    <td><img class='pp-img' src='../php/images/<?php echo $row['img']?>'></td>
                    <td><?php echo $row['username']?></td>
                    <td><?php echo $row['email']?></td>
                    <td class="caps"><?php echo $row['gender']?></td>
                    <td><?php echo $row['year']?></td>
                    <td class='caps'><?php echo $row['class']?></td>
                    <td class='caps'><?php echo $row['cnumber']?></td>
                    <td><?php echo $row['phone']?></td>
                    <td class='caps'><?php echo $row['role']?></td>
                    <td class='caps'><?php echo $ro['postiton']?></td>
                    <td class='caps'><?php echo $row['status']?></td>
                    <td class="eds <?= activeEdit() ?>">
                        <a class="ed" href="../php/useredit.php?id=<?php echo $row['user_id']?>">EDIT</a>
                    </td>
                    <td class="eds <?= activeEdit() ?>">
                        <a class='de <?= activeEdit() ?>' href='../php/delete.php?id=<?php echo $row['user_id']?>'>BLOCK</a>
                    </td>
                </tr>
            <?php
                  }
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
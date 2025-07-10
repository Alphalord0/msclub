<section id="interface">
<div class="interhead">
            <div class="inter1">
                <i onclick="myBtn()" class="barrs fa fa-bars"></i>
                    <p>Dashboard/Class</p>
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
                <div class="inter-name">
                    <div class="inter-name1">
                        <h3 class="i-name">Class</h3>
                        <p>Manage your pending requests here.</p>
                        <p>You can accept or reject requests from this page.</p>
                    </div>

                    <div class="inter-name2">
                        <div class="value">
                            <a class="box">
                                <div class="va-box">
                                    <span>Class</span>
                                    <div class="tota">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40 " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user h-10 w-10 text-primary" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="10" r="3"></circle><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path></svg>
                                        <?php
                                            $query = "SELECT class FROM users";
                                            $query_num = mysqli_query($conn, $query);

                                            $ro = mysqli_num_rows($query_num);

                                            echo "<h3>$ro </h3>";
                                        ?>
                                    </div>
                                    <p>Total number of members in each class in the club</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>



                <div class="values">
                    <a class="box" href="?tab=science">
                        <div class="val-box">
                            <span>Science Students</span>
                            <div class="total">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40 " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user h-10 w-10 text-primary" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="10" r="3"></circle><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path></svg>
                            
                            <?php
                                           // Create a query string
                                    $query = "SELECT COUNT(*) FROM users WHERE class = 'science'";

                                        // Execute the query
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        $ro = $result->fetch_row()[0];
                                            echo "<h3>$ro </h3>" ;
                                    }
                                        ?>
                            
                        </div>
                        <p>Total number of Science</p>
                        </div>
                    </a>

                    <a class="box" href="?tab=business">
                        <div class="val-box">
                            <span>Business Students</span>
                            <div class="total">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40 " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user h-10 w-10 text-primary" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="10" r="3"></circle><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path></svg>
                            
                            <?php
                                           // Create a query string
                                    $query = "SELECT COUNT(*) FROM users WHERE class = 'business'";

                                        // Execute the query
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        $ro = $result->fetch_row()[0];
                                            echo "<h3>$ro </h3>" ;
                                    }
                                        ?>
                            
                        </div>
                        <p>Total number of Business Students</p>
                        </div>
                    </a>

                    <a class="box" href="?tab=general">
                        <div class="val-box">
                            <span>General Arts Students</span>
                            <div class="total">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40 " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user h-10 w-10 text-primary" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="10" r="3"></circle><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path></svg>
                            
                            <?php
                                           // Create a query string
                                    $query = "SELECT COUNT(*) FROM users WHERE class = 'general arts'";

                                        // Execute the query
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        $ro = $result->fetch_row()[0];
                                            echo "<h3>$ro </h3>" ;
                                    }
                                        ?>
                            
                        </div>
                        <p>Total number of General Arts Students</p>
                        </div>
                    </a>

                    <a class="box" href="?tab=agric">
                        <div class="val-box">
                            <span>Agric Students</span>
                            <div class="total">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40 " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user h-10 w-10 text-primary" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="10" r="3"></circle><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path></svg>
                            
                            <?php
                                           // Create a query string
                                    $query = "SELECT COUNT(*) FROM users WHERE class = 'agric'";

                                        // Execute the query
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        $ro = $result->fetch_row()[0];
                                            echo "<h3>$ro </h3>" ;
                                    }
                                        ?>
                            
                        </div>
                        <p>Total number of Agric Student</p>
                        </div>
                    </a>


                    <a class="box" href="?tab=visual">
                        <div class="val-box">
                            <span>Visual Art Students</span>
                            <div class="total">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40 " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user h-10 w-10 text-primary" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="10" r="3"></circle><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path></svg>
                            
                            <?php
                                           // Create a query string
                                    $query = "SELECT COUNT(*) FROM users WHERE class = 'visual arts'";

                                        // Execute the query
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        $ro = $result->fetch_row()[0];
                                            echo "<h3>$ro </h3>" ;
                                    }
                                        ?>
                            
                        </div>
                        <p>Total number of Visual Arts Student</p>
                        </div>
                    </a>


                    <a class="box" href="?tab=technical">
                        <div class="val-box">
                            <span>Technical Students</span>
                            <div class="total">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40 " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user h-10 w-10 text-primary" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="10" r="3"></circle><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path></svg>
                            
                            <?php
                                           // Create a query string
                                    $query = "SELECT COUNT(*) FROM users WHERE class = 'technical'";

                                        // Execute the query
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        $ro = $result->fetch_row()[0];
                                            echo "<h3>$ro </h3>" ;
                                    }
                            ?>
                            
                        </div>
                        <p>Total number of Technical Students</p>
                        </div>
                    </a>

                    <a class="box" href="?tab=econo">
                        <div class="val-box">
                            <span>Home Economics Students</span>
                            <div class="total">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40 " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user h-10 w-10 text-primary" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="10" r="3"></circle><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path></svg>
                            
                            <?php
                                           // Create a query string
                                    $query = "SELECT COUNT(*) FROM users WHERE class = 'h.economics'";

                                        // Execute the query
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        $ro = $result->fetch_row()[0];
                                            echo "<h3>$ro </h3>" ;
                                    }
                                        ?>
                            
                        </div>
                        <p>Total number of Economics</p>
                        </div>
                    </a>
                </div>
</section>
<?php include "footer.php";?>
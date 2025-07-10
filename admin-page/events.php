<?php
  include("./connection/DB.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Notifications</title>
    <script src="./assets/js/jquery.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
</head>

<body>
    <section id="interface">
    <div class="interhead">
        <div class="inter1">
          <i onclick="myBtn()" class="barrs fa fa-bars"></i>
          <p>Dashboard/Notifications</p>
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
         $deactive_notifications = "Select * from inf where active = 0 ORDER BY n_id DESC";
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
                       <h6>New notification may come but not show. Please check notifications on regular bases</h6>
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
                         <h6>New notification may come but not show. Pls check notifications on regular bases</h6>
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
              <div class="inter-nam">
                <div class="inter-name1">
                    <h3 class="i-name">Notifications</h3>
                    <p>You can send notification to all users here.</p>
                </div>

                <div class="inter-name2">
                        <div class="value">
                            <a class="box">
                                <div class="va-box">
                                    <span>Total Event Notifications</span>
                                    <div class="tota">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-pen h-10 w-10 text-primary" aria-hidden="true"><path d="M2 21a8 8 0 0 1 10.821-7.487"></path><path d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"></path><circle cx="10" cy="8" r="5"></circle></svg>
                                        <?php
                                            $query = "SELECT n_id FROM inf WHERE active = '1' ORDER BY n_id";
                                            $query_num = mysqli_query($conn, $query);

                                            $ro = mysqli_num_rows($query_num);

                                            echo "<h3>$ro </h3>";
                                        ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
              </div>

      
               <div class="container">
                      
                      <form class="form-horizontal" id="frm_data">
                          <div class="form-group row">
                             <label class="control-label col-md-4" for="notification">Notification header</label>
                             <div class="col-md-6">
                               <input type="text" name="notifications_name" id="notifications_name" class="form-control" placeholder="Notification header" required/>
                             </div> 
                          </div>   
                          <div class="form-group row">
                             <label class="control-label col-md-4" for="notification">Message</label>
                             <div class="col-md-6">
                               <textarea style="resize:none !important; background: inherit; padding:10px; color: inherit; font-size: 18px;"name="message" id="message" rows="10" cols="100" class='form-control' placeholder="Type the notification message here."></textarea>
                             </div> 
                          </div>
                          <div class="form-group row">
                             <div class="col-md-10 col-offset-2" style="text-align:center;">
                             <input type="submit" id="notify" name="submit" class="btn btn-danger" value="NOTIFY"/>
                             </div>
                          </div>   
                      </form>       


                            <div class="not-view">
                              
                              <?php
                                 foreach($notifications_data as $list_rows){?>
                                   <li id="message_items">
                                   <div class="message alert alert-warning" data-id=<?php echo $list_rows['n_id'];?>>
                                    <a href="../php/remove-not.php?id=<?php echo $list_rows['n_id'];?>"><i class="fa fa-close"></i></a>
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
      
      
                            <?php
                                 foreach($deactive_notifications_dump as $list_rows){?>
                                   <li id="message_items">
                                     <div class="message alert alert-danger" data-id=<?php echo $list_rows['n_id'];?>>
                                     <a href="../php/remove-not.php?id=<?php echo $list_rows['n_id'];?>"><i class="fa fa-close"></i></a>
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
                     </div>
            
    </section>
    
<?php include "footer.php";?>
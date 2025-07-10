<?php 
  session_start();
  if(isset($_SESSION['unique_id'])){
    header("location: ./login.php");
  }
?>

<?php include_once "header.php"; ?>
<body>
  <div class="wrap">
    <section class="form signup">
      <header>Mawuli Cyber Club</header>
      <form action="#" method="POST" enctype="multipart/form-data" autocomplete="off">
        <div class="error-text"></div>
        <div class="name-details">
          <div class="field input">
            <label>Full Name</label>
            <input type="text" name="fname" placeholder="Only Family and First name"  maxlength="20" autofocus>
          </div>
          <div class="field input">
            <label>Username</label>
            <input type="text" name="username" maxlength="17" placeholder="Username" >
          </div>
        </div>
        <div class="field input">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="Enter your email" >
        </div>
        <div class="field input">
          <label>Password</label>
          <input type="password" name="password" maxlength="50" placeholder="Enter new password" >
          <i class="fas fa-eye"></i>
        </div>
        <div class="field input">
          <label>Gender</label>
          <select style="width: 100%;" class="room" name="gender" >
              <option>--Select your Gender--</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
            </select>
        </div>
        <div class="field input">
          <label>Class</label>
          <div class="put">

            <select class="room" name="year" >
              <option>--Select your year--</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
            </select>
            <select class="room" name="class" >
                          <option value="Null">--Select Course--</option>
                          <option value="science">Science</option>
                          <option value="general arts">General Arts</option>
                          <option value="business">Business</option>
                          <option value="agric">Agric</option>
                          <option value="technical">Technical</option>
                          <option value="h.economics">Home Economics</option>
                          <option value="visual art">Visual Art</option>
            </select>
            <select class="room" name="cnumber" >
                          <option value="Null">--Select Class Number--</option>
                          <option value="1">1</option>
                          <option value="2">2</option>
                          <option value="3">3</option>
                          <option value="4">4</option>
                          <option value="5">5</option>
                          <option value="6">6</option>
                          <option value="7">7</option>
                          <option value="8">8</option>
                          <option value="9">9</option>
                          <option value="10">10</option>
                          <option value="11">11</option>
            </select>
          </div>
        </div>
        <div class="field input">
          <label>Phone Number</label>
          <input type="text" name="phone"  placeholder="Enter your phone number"  maxlength="13"  >
        </div>
        <div class="field image">
          <label>Select Image</label>
          <input type="file" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" >
        </div>
        <div class="term">
                <input type="checkbox" name="terms" >
                <button type="button" onclick="myFunction()">Terms and Conditions</button>
            </div>
        <div class="field button">
          <input type="submit" name="submit" value="Sign Up">
        </div>
      </form>
      <div class="link">Already signed up? <a href="login.php">Login now</a></div>
    </section>
  </div>


  <div class="terms" id="terms">
    <div class="policy">
        <h3>Terms and Conditions</h3>
        <p>
          <pre>
        TERMS AND CONDITIONS FOR Mawuli Cybersecurity Site

Welcome to Mawuli Cybersecurity club website. By accessing or using our website and chat group, you agree to abide by the following Terms and Conditions. These terms apply to all members, visitors, and users of our platform.

1. MEMBERSHIP ELIGIBILITY
- You must be a registered member of Cybersecurity club to access the chat group and member-only content.
- Registration may require the payment of monthly or annual dues as set by the group.

2. PAYMENT OF DUES
- All members must pay their dues promptly to maintain access to member features, including the chat group.
- Members who fail to pay dues will be temporarily or permanently blocked from accessing the platform.
- Payment terms and deadlines will be communicated through official group channels.

3. CHAT GROUP CONDUCT
To maintain a respectful and professional environment:
- Profane, abusive, or offensive language is strictly prohibited.
- Hate speech, bullying, or harassment will result in an immediate ban.
- Spamming, advertising unrelated services, or sharing malicious links is not allowed.a
- Respect the opinions and privacy of other members.

Violation of these rules may lead to warnings, temporary suspensions, or permanent bans.

4. PRIVACY & DATA PROTECTION
- We respect your privacy. Your data will not be sold or shared without your consent.
- Misuse or unauthorized distribution of member data may lead to termination and legal action.

5. USE OF WEBSITE AND MATERIALS
- All resources on the website are for educational and community-building purposes only.
- Do not share copyrighted material without permission.
- Do not use group materials for personal or commercial gain without approval.

6. TERMINATION OF MEMBERSHIP
- We reserve the right to terminate or restrict access for members who violate these terms.
- Members may voluntarily cancel their membership by contacting the admin team.

7. CHANGES TO TERMS
- We may update these Terms and Conditions at any time.
- Members will be notified of significant changes via email or group announcements.

8. CONTACT INFORMATION
To report violations or for inquiries:
Email: singularityyue@proton.me
Phone: [0536971421]

By continuing to use our platform, you agree to these Terms and Conditions.

</pre>
        </p>
        <button class="closer" id="close">
            <div class="close"></div>
        </button>


    </div>
</div>    

  <script src="javascript/pass-show-hide.js"></script>
  <script src="javascript/signup.js"></script>

</body>
</html>

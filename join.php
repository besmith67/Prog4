<?php 
/* ---------------------------------------------------------------------------
 * filename    : join.php
 * author      : Branden Smith, besmith2@svsu.edu
 * description : This program adds/inserts a new customer (table: customers)
 * ---------------------------------------------------------------------------
 */
session_start();
	
require '../database/database.php';
if ( !empty($_POST)) { // if not first time through
	// initialize user input validation variables
	$nameError = null;
	$emailError = null;
	$mobileError = null;
	$passwordError = null;

	// initialize $_POST variables
	$name = $_POST['name'];
	$email = $_POST['email'];
	$mobile = $_POST['mobile'];
	$password = $_POST['password'];
	$passwordhash = MD5($password);
	
	// initialize $_FILES variables
	$fileName = '';
	$tmpName = '';
	$fileSize = ''; 
	$fileType = '';
	$content = '';

	// validate user input
	$valid = true;
	if (empty($name)) {
		$nameError = 'Please enter Name';
		$valid = false;
	}
	if (empty($email)) {
		$emailError = 'Please enter valid Email Address (REQUIRED)';
		$valid = false;
	} else if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
		$emailError = 'Please enter a valid Email Address';
		$valid = false;
	}
	//Check if email already exists
	$pdo = Database::connect();
	$sql = "SELECT * FROM customers";
	foreach($pdo->query($sql) as $row) {
		if($email == $row['email']) {
			$emailError = 'Email has already been registered!';
			$valid = false;
		}
	}
	Database::disconnect();
	
	
	if (empty($mobile)) {
		$mobileError = 'Please enter Mobile Number ';
		$valid = false;
	}
	if (empty($password)) {
		$passwordError = 'Please enter Password';
		$valid = false;
	}

	// insert data into database
	if ($valid) 
	{
	$fileName = $_FILES['userfile']['name'];
	$tmpName  = $_FILES['userfile']['tmp_name'];
	$fileSize = $_FILES['userfile']['size'];
	$fileType = $_FILES['userfile']['type'];
	$content = file_get_contents($tmpName);
	
		$pdo = Database::connect();
		
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		/* $sql = "INSERT INTO customers (name,email,mobile,password_hash) values(?, ?, ?, ?)";
		$q = $pdo->prepare($sql);
		$q->execute(array($name,$email,$mobile,$passwordhash)); */
		
		$sql = "INSERT INTO customers (name,email,mobile,password_hash,filename,filesize,filetype,filecontent)
					values(?, ?, ?, ?, ?, ?, ?, ?)";
            $q = $pdo->prepare($sql);
            $q->execute(array($name, $email, $mobile, $passwordhash,$fileName,$fileSize,$fileType,$content));
		
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "SELECT * FROM customers WHERE email = ? AND password_hash = ? LIMIT 1";
		$q = $pdo->prepare($sql);
		$q->execute(array($email,$passwordhash));
		$data = $q->fetch(PDO::FETCH_ASSOC);
		
		$_SESSION['custid'] = $data['id'];
		
		Database::disconnect();
		header("Location: customers.php"); //auto sign in and display list after joining
	}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset='UTF-8'>
    <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css' rel='stylesheet'>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js'></script>
</head>

<body>
    <div class="container">

		<div class="span10 offset1">

			<div class="row">
				<h3>Add new Customer</h3>
			</div>
	
			<form class="form-horizontal" action="join.php" method="post" enctype="multipart/form-data">

				<div class="control-group <?php echo !empty($nameError)?'error':'';?>">
					<label class="control-label">Name</label>
					<div class="controls">
						<input name="name" type="text"  placeholder="Name" value="<?php echo !empty($name)?$name:'';?>">
						<?php if (!empty($nameError)): ?>
							<span class="help-inline"><?php echo $nameError;?></span>
						<?php endif; ?>
					</div>
				</div>
				
				
				<div class="control-group <?php echo !empty($emailError)?'error':'';?>">
					<label class="control-label">Email</label>
					<div class="controls">
						<input name="email" type="text" placeholder="Email Address" value="<?php echo !empty($email)?$email:'';?>">
						<?php if (!empty($emailError)): ?>
							<span class="help-inline"><?php echo $emailError;?></span>
						<?php endif;?>
					</div>
				</div>
				
				<div class="control-group <?php echo !empty($mobileError)?'error':'';?>">
					<label class="control-label">Mobile Number</label>
					<div class="controls">
						<input name="mobile" type="text"  placeholder="Mobile Phone Number" value="<?php echo !empty($mobile)?$mobile:'';?>">
						<?php if (!empty($mobileError)): ?>
							<span class="help-inline"><?php echo $mobileError;?></span>
						<?php endif;?>
					</div>
				</div>
				
				<div class="control-group <?php echo !empty($passwordError)?'error':'';?>">
					<label class="control-label">Password</label>
					<div class="controls">
						<input id="password" name="password" type="password"  placeholder="password" value="<?php echo !empty($password)?$password:'';?>">
						<?php if (!empty($passwordError)): ?>
							<span class="help-inline"><?php echo $passwordError;?></span>
						<?php endif;?>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label">Picture</label>
					<div class="controls">
						<input type="hidden" name="MAX_FILE_SIZE" value="16000000">
						<input name="userfile" type="file" id="userfile">
					</div>
					<br />
				</div>
				
				<br />
			  
				<div class="form-actions">
					<button type="submit" class="btn btn-success">Confirm</button>
					<a class="btn btn-secondary" href="login.php">Back</a>
				</div>
				
			</form>
			
		</div> <!-- end div: class="span10 offset1" -->
				
    </div> <!-- end div: class="container" -->
  </body>
</html>

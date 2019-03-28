<?php
/* ---------------------------------------------------------------------------
 * filename    : customers.class.php
 * author      : Branden Smith, besmith2@svsu.edu
 * description : Customer Class that holds all functions used by customers.php
 * ---------------------------------------------------------------------------
 */
class Customer { 
    public $id;
    public $name;
    public $email;
    public $mobile;
	public $password; // text from HTML form
	public $password_hashed; // hashed password
    private $noerrors = true;
    private $nameError = null;
    private $emailError = null;
    private $mobileError = null;
	private $passwordError = null;
    private $title = "Customer";
    private $tableName = "customers";
	
	// initialize $_FILES variables
	private $fileName = '';
	private $tmpName = '';
	private $fileSize = ''; 
	private $fileType = '';
	private $content = '';
	private $fileFullPath = '';
	private $fileDescription = '';
	private $fileDescriptionError = null;
	
    function upload_file() {
			//$fileLocation = "/home/besmith2/public_html/cis355/Prog4/uploads/";
			$fileLocation = "uploads/";
			$this->fileFullPath = $fileLocation . $this->fileName; 
			if (!file_exists($fileLocation))
				mkdir ($fileLocation); // create subdirectory, if necessary 

			// if file does not already exist, upload it
			if (!file_exists($this->fileFullPath)) {
				$result = move_uploaded_file($this->tmpName, $this->fileFullPath);
				if ($result) {
					//echo "File <b><i>" . $this->fileName 
					//	. "</i></b> has been successfully uploaded.";
					// code below assumes filepath is same as filename of this file
					// minus the 12 characters of this file, "upload01.php"
					// plus the string, $fileLocation, i.e. "uploads/"
					/* echo "<br>To see all uploaded files, visit: " 
							. "<a href='"
							. substr(get_current_url(), 0, -12)
							. "$fileLocation'>" 
							. substr(get_current_url(), 0, -12) 
							. "$fileLocation</a>"; */
				} else {
					echo "Upload denied for file. " . $this->fileName 
						. "</i></b>. Verify file size < 2MB. ";
				}
			}
			// otherwise, show error message
			else {
				echo "File <b><i>" . $this->fileName 
					. "</i></b> already exists. Please rename file.";
			}
		
		
	}// end function upload_file()
	
	
    function create_record() { // display "create" form
        $this->generate_html_top (1);
        $this->generate_form_group("name", $this->nameError, $this->name, "autofocus");
        $this->generate_form_group("email", $this->emailError, $this->email);
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile);
		$this->generate_form_group("password", $this->passwordError, $this->password, "", "password");
		$this->insert_photo();
		$this->generate_form_group("filedescription", $this->fileDescriptionError, $this->fileDescription);
        $this->generate_html_bottom (1);
    } // end function create_record()
    
    function read_record($id) { // display "read" form
        $this->select_db_record($id);
        $this->generate_html_top(2);
		$this->display_photo();
        $this->generate_form_group("name", $this->nameError, $this->name, "disabled");
        $this->generate_form_group("email", $this->emailError, $this->email, "disabled");
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile, "disabled");
		$this->generate_form_group("filedescription", $this->fileDescriptionError, $this->fileDescription, "disabled");
        $this->generate_html_bottom(2);
    } // end function read_record()
    
    function update_record($id) { // display "update" form
        if($this->noerrors) $this->select_db_record($id);
        $this->generate_html_top(3, $id); 
		$this->display_photo();
        $this->generate_form_group("name", $this->nameError, $this->name, "autofocus onfocus='this.select()'");
        $this->generate_form_group("email", $this->emailError, $this->email);
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile);
		$this->insert_photo();
		$this->generate_form_group("filedescription", $this->fileDescriptionError, $this->fileDescription);
        $this->generate_html_bottom(3);
    } // end function update_record()
    
    function delete_record($id) { // display "read" form
        $this->select_db_record($id);
        $this->generate_html_top(4, $id);
		$this->display_photo();
        $this->generate_form_group("name", $this->nameError, $this->name, "disabled");
        $this->generate_form_group("email", $this->emailError, $this->email, "disabled");
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile, "disabled");
		$this->generate_form_group("filedescription", $this->fileDescriptionError, $this->fileDescription, "disabled");
        $this->generate_html_bottom(4);
    } // end function delete_record()
	
	function display_photo () {
		echo "
				<div class='control-group col-md-6'>
					<div class='controls '>
			";
					 
		if ($this->fileSize > 0) 
			echo "<img height='15%' width='15%' src='data:image/jpeg;base64," . 
				base64_encode( $this->content ) . "' />"; 
		else 
			echo 'No photo on file.';	
		echo"	
					</div>
					<br>
				</div>
			";
	}
	
	function insert_photo () {
		echo "<div class='control-group '>
							<label class='control-label'>Picture</label>
							<div class='controls'>
								<input type='hidden' name='MAX_FILE_SIZE' value='16000000'>
								<input name='userfile' type='file' id='userfile'>
							</div>
							<br>
				</div>";
	}
    
	//Inserts form input into database
    function insert_db_record () { 
		// initialize $_FILES variables
		$this->fileName = $_FILES['userfile']['name'];
		$this->tmpName  = $_FILES['userfile']['tmp_name'];
		$this->fileSize = $_FILES['userfile']['size'];
		$this->fileType = $_FILES['userfile']['type'];
		$this->content = file_get_contents($this->tmpName); 
		$this->fileDescription = $_POST['filedescription'];
		
        if ($this->fieldsAllValid()) { // validate user input
            // if valid data, insert record into table
			$this->upload_file();
            $pdo = Database::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->password_hashed = MD5($this->password);
            $sql = "INSERT INTO $this->tableName (name,email,mobile,password_hash,filename,filesize,filetype,filecontent,filepath,filedescription)
					values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $q = $pdo->prepare($sql);
            $q->execute(array($this->name, $this->email, $this->mobile, $this->password_hashed,$this->fileName,$this->fileSize,$this->fileType,$this->content,$this->fileFullPath,$this->fileDescription));
            Database::disconnect();
			
            header("Location: $this->tableName.php"); // go back to "list"
        }
        else {
            // if not valid data, go back to "create" form, with errors
            // Note: error fields are set in fieldsAllValid ()method
            $this->create_record(); 
        }
    } // end function insert_db_record
    
	//Retrieves data from database to display and read
    private function select_db_record($id) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM $this->tableName where id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        Database::disconnect();
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->mobile = $data['mobile'];
		$this->fileName = $data['filename'];
        $this->fileSize = $data['filesize'];
        $this->fileType = $data['filetype'];
		$this->content = $data['filecontent'];
		$this->fileDescription = $data['filedescription'];
		
    } // function select_db_record()
    
	//Updates values of selected record
       function update_db_record ($id) {
		// initialize $_FILES variables
		$this->fileName = $_FILES['userfile']['name'];
		$this->tmpName  = $_FILES['userfile']['tmp_name'];
		$this->fileSize = $_FILES['userfile']['size'];
		$this->fileType = $_FILES['userfile']['type'];
		if($this->fileSize > 0) {
		    $this->content = file_get_contents($this->tmpName); 
		}
        $this->id = $id;
		$this->fileDescription = $_POST['filedescription'];
		
        if ($this->fieldsSomeValid()) {
			if($this->fileSize > 0) {
				$this->noerrors = true;
				$this->upload_file();
				$pdo = Database::connect();
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$sql = "UPDATE $this->tableName  set name = ?, email = ?, mobile = ?, filename = ?, filesize = ?, filetype = ?, filecontent = ?, filepath = ?, filedescription = ? WHERE id = ?";
				$q = $pdo->prepare($sql);
				$q->execute(array($this->name,$this->email,$this->mobile,$this->fileName,$this->fileSize,$this->fileType,$this->content,$this->fileFullPath,$this->fileDescription,$this->id));
				Database::disconnect();
				header("Location: $this->tableName.php");
			} else {
				$this->noerrors = true;
				$pdo = Database::connect();
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$sql = "UPDATE $this->tableName  set name = ?, email = ?, mobile = ? WHERE id = ?";
				$q = $pdo->prepare($sql);
				$q->execute(array($this->name,$this->email,$this->mobile,$this->id));
				Database::disconnect();
				header("Location: $this->tableName.php");
			}
        }
        else {
            $this->noerrors = false;
            $this->update_record($id);  // go back to "update" form
        }
    } // end function update_db_record
    
	//Deletes selected database record
    function delete_db_record($id) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM $this->tableName WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        Database::disconnect();
        header("Location: $this->tableName.php");
    } // end function delete_db_record()
    
	//Creates top part of each form based on inputted function
    private function generate_html_top ($fun, $id=null) {
        switch ($fun) {
            case 1: // create
                $funWord = "Create"; $funNext = "insert_db_record"; 
                break;
            case 2: // read
                $funWord = "Read"; $funNext = "none"; 
                break;
            case 3: // update
                $funWord = "Update"; $funNext = "update_db_record&id=" . $id; 
                break;
            case 4: // delete
                $funWord = "Delete"; $funNext = "delete_db_record&id=" . $id; 
                break;
            default: 
                echo "Error: Invalid function: generate_html_top()"; 
                exit();
                break;
        }
        echo "<!DOCTYPE html>
        <html>
            <head>
                <title>$funWord a $this->title</title>
                    ";
        echo "
                <meta charset='UTF-8'>
                <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css' rel='stylesheet'>
                <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js'></script>
                <style>label {width: 5em;}</style>
                    "; 
        echo "
            </head>";
        echo "
            <body>
                <div class='container'>
                    <div class='span10 offset1'>
                        <p class='row'>
                            <h3>$funWord a $this->title</h3>
                        </p>
                        <form class='form-horizontal' action='$this->tableName.php?fun=$funNext' method='post' enctype='multipart/form-data'>                        
                    ";
    } // end function generate_html_top()
    
	//Creates bottom part of each form based on inputted function
    private function generate_html_bottom ($fun) {
        switch ($fun) {
            case 1: // create
                $funButton = "<button type='submit' class='btn btn-success'>Create</button>"; 
                break;
            case 2: // read
                $funButton = "";
                break;
            case 3: // update
                $funButton = "<button type='submit' class='btn btn-warning'>Update</button>";
                break;
            case 4: // delete
                $funButton = "<button type='submit' class='btn btn-danger'>Delete</button>"; 
                break;
            default: 
                echo "Error: Invalid function: generate_html_bottom()"; 
                exit();
                break;
        }
        echo " 
                            <div class='form-actions'>
                                $funButton
                                <a class='btn btn-secondary' href='$this->tableName.php'>Back</a>
                            </div>
                        </form>
                    </div>

                </div> <!-- /container -->
            </body>
        </html>
                    ";
    } // end function generate_html_bottom()
    
	 //Creates appropriate form based on inputted value
	 private function generate_form_group ($label, $labelError, $val, $modifier="", $fieldType="text") {
        echo "<div class='form-group";
        echo !empty($labelError) ? ' alert alert-danger ' : '';
        echo "'>";
        echo "<label class='control-label'>$label &nbsp;</label>";
        echo "<input "
            . "name='$label' "
            . "type='$fieldType' "
            . "$modifier "
            . "placeholder='$label' "
            . "value='";
        echo !empty($val) ? $val : '';
        echo "'>";
        if (!empty($labelError)) {
            echo "<span class='help-inline'>";
            echo "&nbsp;&nbsp;" . $labelError;
            echo "</span>";
        }
        echo "</div>"; // end div: class='form-group'
    } // end function generate_form_group()
    
	//Checks if every value is valid and not empty in Form
    private function fieldsAllValid () {
        $valid = true;
        if (empty($this->name)) {
            $this->nameError = 'Please enter Name';
            $valid = false;
        }
        if (empty($this->email)) {
            $this->emailError = 'Please enter Email Address';
            $valid = false;
        } 
        else if ( !filter_var($this->email,FILTER_VALIDATE_EMAIL) ) {
            $this->emailError = 'Please enter a valid email address: me@mydomain.com';
            $valid = false;
        }
        if (empty($this->mobile)) {
            $this->mobileError = 'Please enter Mobile phone number';
            $valid = false;
        }
		if (empty($this->password)) {
            $this->passwordError = 'Please enter Password phone number';
            $valid = false;
        }
		if ($this->fileType != "image/jpg" && $this->fileType != "image/png" && $this->fileType != "image/jpeg" && $this->fileType != "image/gif" && !empty($this->fileType)) {
			  $valid = false;
		} 
        return $valid;
		
    } // end function fieldsAllValid() 
	private function fieldsSomeValid () {
        $valid = true;
        if (empty($this->name)) {
            $this->nameError = 'Please enter Name';
            $valid = false;
        }
        if (empty($this->email)) {
            $this->emailError = 'Please enter Email Address';
            $valid = false;
        } 
        else if ( !filter_var($this->email,FILTER_VALIDATE_EMAIL) ) {
            $this->emailError = 'Please enter a valid email address: me@mydomain.com';
            $valid = false;
        }
        if (empty($this->mobile)) {
            $this->mobileError = 'Please enter Mobile phone number';
            $valid = false;
        }
		if ($this->fileType != "image/jpg" && $this->fileType != "image/png" && $this->fileType != "image/jpeg" && $this->fileType != "image/gif" && !empty($this->fileType)) {
			  $valid = false;
		} 
        return $valid;
		
    } // end function fieldsSomeValid()
    
	//Displays main list of records from database
    function list_records() {
        echo "<!DOCTYPE html>
        <html>
            <head>
                <title>$this->title" . "s" . "</title>
                    ";
        echo "
                <meta charset='UTF-8'>
                <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css' rel='stylesheet'>
                <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js'></script>
                    ";  
        echo "
            </head>
            <body>
                <a href='https://github.com/besmith67/Prog4.git' target='_blank'>Github</a><br />
				<a href='Prog4_UML.png' target='_blank'>UML</a>
				<a href='Prog04_Diagram.txt' target='_blank'>Screen Flow</a><br />
				<a href='uploads/' target='_blank'>All Uploads</a>
                <div class='container'>
                    <p class='row'>
                        <h3>$this->title" . "s" . "</h3>
                    </p>
                    <p>
                        <a href='$this->tableName.php?fun=display_create_form' class='btn btn-success'>Create</a>
						<a href='$this->tableName.php?fun=show_images' class='btn btn-info'>Show Images</a>
						<a href='logout.php' class='btn btn-warning'>Logout</a>
						
                    </p>
                    <div class='row'>
                        <table class='table table-striped table-bordered'>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                    ";
        $pdo = Database::connect();
        $sql = "SELECT * FROM $this->tableName ORDER BY id DESC";
        foreach ($pdo->query($sql) as $row) {
            echo "<tr>";
            echo "<td>". $row["name"] . "</td>";
            echo "<td>". $row["email"] . "</td>";
            echo "<td>". $row["mobile"] . "</td>";
            echo "<td width=250>";
            echo "<a class='btn btn-info' href='$this->tableName.php?fun=display_read_form&id=".$row["id"]."'>Read</a>";
            echo "&nbsp;";
            echo "<a class='btn btn-warning' href='$this->tableName.php?fun=display_update_form&id=".$row["id"]."'>Update</a>";
            echo "&nbsp;";
            echo "<a class='btn btn-danger' href='$this->tableName.php?fun=display_delete_form&id=".$row["id"]."'>Delete</a>";
            echo "</td>";
            echo "</tr>";
        }
        Database::disconnect();        
        echo "
                            </tbody>
                        </table>
                    </div>
                </div>
				

            </body>

        </html>
                    ";  
    } // end function list_records()
	
	function show_images() {
		echo "<!DOCTYPE html>
			<html>
				<head>
					<title>Images</title>
						";
			echo "
					<meta charset='UTF-8'>
					<link href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css' rel='stylesheet'>
					<script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js'></script>
						";  
			echo "
				</head>
				<body>
				<div class='container'>

						<p class='row'>
							<h3>Images</h3>
						</p>
						<p>

							<a class='btn btn-secondary' href='$this->tableName.php'>Back</a>

						</p>
						<div class='row'>
							<table class='table table-striped table-bordered'>
								<thead>
									<tr>
										<th>FileName</th>
										<th>FilePath</th>
										<th>Description</th>
										<th>Photo</th>
									</tr>
								</thead>
								<tbody>
						";
			$pdo = Database::connect();
			$sql = "SELECT * FROM $this->tableName WHERE filesize > 0 ORDER BY id DESC";
			foreach ($pdo->query($sql) as $row) {
				echo "<tr>";
				echo "<td>". $row["filename"] . "</td>";
				echo "<td>". $row["filepath"] . "</td>";
				echo "<td>". $row["filedescription"] . "</td>";
				echo "<td>". $row["name"]."<br /><img height='100' width='100' src='data:image/jpeg;base64,"
							. base64_encode( $row['filecontent'] ). "'/>"
							. "</td>";
				echo "</tr>";
				
				/* echo "<img height='15%' width='15%' src='data:image/jpeg;base64," . 
				base64_encode( $this->content ) . "' />"; */
			}
			Database::disconnect();        
			echo "
								</tbody>
							</table>
						</div>
					</div>
					

				</body>

			</html>	";
		
	}
    
} // end class Customer

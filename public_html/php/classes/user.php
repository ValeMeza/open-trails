<?php

require_once(dirname(__DIR__)  .  "/traits/anti-abuse.php");
require_once "autoload.php";

/**
 * Trail Quail user class -- this is where user information is stored
 * This is part of the Trail Quail web application.  This feature will determine who the registered users
 * are and what level of access they have.  It will also store the userId, the user's name, their email address,
 * their browsing information, and datetime stamp when their account was set up.
 *
 * author Jeff Saul <scaleup13@gmail.com>
 */
class User  {
	use AntiAbuse;

	/**
	 * user Id is an unsigned integer; this is the primary key for class user
	 * @var integer $userId
	 */
	private $userId;

	/**
	 * This indicates what type of account and what type of access each user has
	 * This is a string of length 1
	 * @var string $userAccountType
	 */
	private $userAccountType;

	/**
	 * This is the user's email address
	 * @var string $userEmail
	 */
	private $userEmail;

	/**
	 * This is the 128 byte hash variable for user authentication
	 * @var string $userHash
	 */
	private $userHash;

	/**
	 * This is the user's username
	 * @var string $userName
	 */
	private $userName;

	/**
	 * This is the 64 byte salt variable for user authentication
	 * @var string $userSalt
	 */
	private $userSalt;

	/**
	 * Constructor for user - store information on each user and superuser
	 * This set of methods will check the input data for each user/superuser
	 *
	 * @param mixed $newUserId -- user account id, may be null if user is new
	 * @param string $newBrowser -- information on the user browser
	 * @param datetime $newCreateDate -- date the user account was set up
	 * @param binary $newIpAddress -- the user Ip address when account was set up
	 * @param string $newUserAccountType -- this denotes the type of user account
	 * @param string $newUserEmail -- the user email address for this account
	 * @param string $newUserHash -- hash value of user password
	 * @param string $newUserName -- the user username for this account
	 * @param string $newUserSalt -- salt value of user password
	 *
	 * @throws invalidArgumentException if data types are not valid
	 * @throws rangeException if data values are out of bounds (e.g. strings too long or too short)
	 * @throws Exception if some other exception is thrown
	 */
	public function _construct($newUserId, $newBrowser, $newCreateDate, $newIpAddress, $newUserAccountType, $newUserEmail, $newUserHash, $newUserName, $newUserSalt) {
		try {
			$this->setUserID($newUserId);
			$this->setBrowser($newBrowser);
			$this->setCreateDate($newCreateDate);
			$this->setIpAddress($newIpAddress)
			$this->setUserAccountType($newUserAccountType);
			$this->setUserEmail($newUserEmail);
			$this->setUserHash($newUserHash);
			$this->setUserName($newUserName);
			$this->setUserSalt($newUserSalt);
		}
		catch(InvalidArgumentException $invalidArgument) {
			// rethrow the exception to the caller
			throw(new InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(RangeException $range) {
			// rethrow the exception to the caller
			throw(new RangeException($range->getMessage(), 0,$range));
		} catch(Exception $exception) {
			// rethrow generic exception 
			throw(new Exception($exception->getMessage(), 0, $exception));
		}
	}

	/**
	 * These are the data validation accessors and mutators for user
	 */

	/**
	 * accessor method for user id
	 *
	 * @return mixed value of user id
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * mutator method for user id
	 *
	 * @param mixed $newUserId new value of user id
	 * @throws InvalidArgumentException if $newUserId is not an integer
	 * @throws RangeException if $newUserId is not positive
	 */
	public function setUserId($newUserId) {
		//  base case:  if the user id is null, this a new user without a mySQL assigned id at this time
		if($newUserId === null) {
			$this->userId = null;
			return;
		}

		// verify that the user id is an integer
		$newUserId = filter_var($newUserId, FILTER_VALIDATE_INT);
		if($newUserId === false) {
			throw(new InvalidArgumentException("user id is not a valid integer"));
		}

		// verify that the user id is positive
		if($newUserId <= 0) {
			throw(new RangeException("user id is not positive"));
		}

		// convert and store the user id
		$this->userId = intval($newUserId);
	}

	/**
	 * accessor method for user account type (regular-r, poweruser-p, suspended-x)
	 *
	 * @return string $newUserAccountType - 1 byte string value of user account type
	 */
	public function getUserAccountType() {
		return ($this->userAccountType);
		}

	/**
	 * mutator method for user account type (regular-r, power user-p, suspended-x)
	 *
	 * @param string $newUserAccountType - 1 byte string value of user account type
	 * @throws InvalidArgumentException if $newUserAccountType is not a string or is insecure
	 * @throws RangeException if $newUserAccountType is > 1 character
	 */
	public function setUserAccountType($newUserAccountType) {
		// verify that the user account type is secure
		$newUserAccountType = trim($newUserAccountType);
		$newUserAccountType = filter_var($newUserAccountType, FILTER_SANITIZE_STRING);
		if(empty($newUserAccountType) === true) {
			throw(new InvalidArgumentException("User account type is empty or insecure"));
		}

		// verify the user account type will fit in the database
		if(strlen($newUserAccountType) > 1) {
			throw(new RangeException("User account type is too large"));
		}

		//store user account type
		$this->userAccountType = $newUserAccountType;
	}

	/**
	 * accessor method for user email
	 *
	 * @return string $newUserEmail - user email address
	 */
	public function getUserEmail() {
		return ($this->userEmail);
	}

	/**
	 * mutator method for user email address
	 *
	 * @param string $newUserEmail -- user email address
	 * @throws InvalidArgumentException if email address does not pass sanitation
	 * @throws RangeException if email is longer than 128 characters
	 */
	public function setUserEmail($newUserEmail) {
		// verify that email address is valid
		$newUserEmail = trim($newUserEmail);
		$newUserEmail = filter_var($newUserEmail, FILTER_SANITIZE_EMAIL);
		if(empty($newUserEmail) === true) {
			throw new InvalidArgumentException ("user email invalid");
		}

		// verify that user email address will fit in the database
		if(strlen($newUserEmail) > 128) {
			throw (new RangeException ("User email address is too large"));
		}

		// store the user's email address
		$this->userEmail = $newUserEmail;
	}

	/**
	 * accessor method for userHash
	 *
	 * @return string $newUserHash -- value of user hash
	 */
	public function getUserHash() {
		return ($this->userHash);
	}

	/**
	 * mutator method for userHash
	 *
	 * @param string $newUserHash -- new value of user hash
	 * @throws InvalidArgumentException if $newUserHash is empty, not in hexadecimal, or is insecure
	 * @throws RangeException if $newUserHash is not 128 characters in length
	 */
	public function setUserHash($newUserHash) {
		// Verify that user hash is valid
		$newUserHash = trim($newUserHash);
		$newUserHash = filter_var($newUserHash, FILTER_SANITIZE_STRING);
		if(empty($newUserHash) === true) {
			throw(new InvalidArgumentException("User hash is empty or insecure."));
		}

		// Verify that user hash is hexadecimal
		if((ctype_xdigit($newUserHash))=== false) {
			throw (new InvalidArgumentException("User hash is not hexadecimal."));
		}

		// Verify that user hash has correct length = 128
		if(strlen($newUserHash) !== 128) {
			throw(new RangeException("User hash is not the right length"));
		}

		//  store the user hash
		$this->userHash = $newUserHash;
	}

	/**
	 * accessor method for user name - userName
	 *
	 * @return string $newUserName - user name
	 */
	public function getUserName() {
		return ($this->userName);
	}

	/**
	 * mutator method for user name - $newUserName
	 *
	 * @param string $newUserName -- new value of user name
	 * @throws InvalidArgumentException if $newUserName is not a string or insecure
	 * @throws RangeException if $newUserName length is > 64 characters
	 */
	public function setUserName($newUserName) {
		// Verify that the user name is secure
		$newUserName = trim($newUserName);
		$newUserName = filter_var($newUserName, FILTER_SANITIZE_STRING);
		if(empty($newUserName) === true) {
			throw(new InvalidArgumentException("User name is empty or insecure."));
		}

		// verify that the user name will fit in the database
		if(strlen($newUserName) > 64) {
			throw(new RangeException("User name is too long."));
		}

		// store user name
		$this->userName = $newUserName;
	}

	/**
	 * accessor method for user salt -- $newUserSalt
	 *
	 * @return string value of user salt
	 */
	public function getUserSalt() {
		return $this->userSalt;
	}

	/**
	 * mutator method for user salt -- $newUserSalt
	 *
	 * @param string $newUserSalt -- new value of user salt
	 * @throws InvalidArgumentException if $newUserSalt is empty, not in hexadecimal, or is insecure
	 * @throws RangeException if $newUserSalt is not 64 characters in length
	 */
	public function setUserSalt($newUserSalt) {
		// Verify that user salt is valid
		$newUserSalt = trim($newUserSalt);
		$newUserSalt = filter_var($newUserSalt, FILTER_SANITIZE_STRING);
		if(empty($newUserSalt) === true) {
			throw(new InvalidArgumentException("User salt is empty or insecure."));
		}

		// Verify that user salt is hexadecimal
		if((ctype_xdigit($newUserSalt))=== false) {
			throw (new InvalidArgumentException("User salt is not hexadecimal."));
		}

		// Verify that user salt has correct length = 64
		if(strlen($newUserSalt) !== 64) {
			throw(new RangeException("User salt is not the right length"));
		}

		//  store the user salt
		$this->userSalt = $newUserSalt;
	}

	/**
	 * Inserts this user's ID information into mySQL
	 *
	 * @param PDO $pdo -- pointer to PDO connection, by reference
	 * @throws PDOException when mySQL relates errors occur
	 */
	public function insert(PDO $pdo) {
		// check to see if the userId is null
		If($this->commentId !== null) {
			throw(new PDOException("not a new user"));
		}

		// create user template
		$query = "INSERT INTO user(userId, browswer, createDate, ipAddress, userAccountType, userEmail, userHash, userName, userSalt ) VALUES (:userId, :browser, :createDate, :ipAddress, :userAccountType, :userEmail, :userHash, :userName, :userSalt)";
		$statement = $pdo->prepare($query);


		//bind the member variables to the placeholders in the temlate
		$formattedDate = $this->createDate->format("Y-m-d H:i:s");
		$parameters = ["userId" => $this->userId,"browser" => $this->browser, "createDate" => $formattedDate, "ipAddress" => $this->ipAddress, "userAccountType" => $this->userAccountType, "userEmail" => $this->userEmail,"userHash" => $this->userHash, "userName" => $this->userName, "userSalt" => $this->userSalt];
		$statement->execute($parameters);

		// update the null userId with what mySQL just gave us
		$this->userId = intval($pdo->lastInsertId());
	}

	/**
	 *
	 */
}
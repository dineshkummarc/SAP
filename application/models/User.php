<?php
/**
 * User: peaceman
 * Date: 4/16/12
 * Time: 11:02 PM
 */
class Application_Model_User extends \SAP\Model\AbstractModel
{
	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->_get('username');
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username)
	{
		$this->_set('username', $username);
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->_get('password');
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->_set('password', $password);
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->_get('email');
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->_set('email', $email);
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->_getDate('created_at');
	}

	/**
	 * @param string|\DateTime $createdAt
	 */
	public function setCreatedAt($createdAt)
	{
		$this->_setDate('created_at', $createdAt);
	}

	/**
	 * @return \DateTime
	 */
	public function getUpdatedAt()
	{
		return $this->_getDate('updated_at');
	}

	/**
	 * @param string|\DateTime $updatedAt
	 */
	public function setUpdatedAt($updatedAt)
	{
		$this->_setDate('updated_at', $updatedAt);
	}

	/**
	 * @param string $cleartextPassword
	 * @throws \RuntimeException
	 */
	public function setAndSaltPassword($cleartextPassword)
	{
		$username = $this->getUsername();
		if ($username === null) {
			throw new \RuntimeException('need username to salt password, given username is null');
		}

		$hashedAndSaltedPassword = sha1(sha1($cleartextPassword) . sha1($this->getUsername()));
		$this->setPassword($hashedAndSaltedPassword);
	}

	public function preUpdate()
	{
		if (null === $this->_get('created_at')) {
			$this->_set('created_at', new \DateTime());
		}

		$this->_setDate('updated_at', new \DateTime());
	}
}

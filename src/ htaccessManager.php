<?php
/* MIT License
 * 
 * Copyright (c) 2015 Máté Cserép
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * @var string DEFAULT_PASSWD_DIR The default location for htpasswd files.
 */
define('DEFAULT_PASSWD_DIR', '/path/to/passwd/folder');

/**
 * Defines a htaccess manager with basic authentication.
 *
 * Example usage assuming authentication was enforced on page load by htaccess:
 * <code>
 * <?php
 * require_once 'htaccessManager.php';
 * $manager = new htaccessManager('example', array('admin1', 'admin2'));
 * $manager->show($_SERVER['PHP_AUTH_USER']); 
 * ?>
 * </code>
 * 
 * @author Máté Cserép <mcserep@gmail.com>
 */
class htaccessManager
{
  /**
   * @var string Stores the name of the repository.
   */
  private $reposName;
  /**
   * @var string Stores the path to the managed htpasswd file.
   */
  private $passwdPath;
  /**
   * @var array Stores the administrator names.
   */
  private $adminNames;
  /**
   * @var array Stores the user names.
   */
  private $userNames;

  /**
   * Creates a htaccessManager instance.
   * @param string $reposName The name of the repository.
   * @param array $adminNames The users with administrator privileges.
   * @param string $passwdDir The directory storing the managed htpasswd file. (Defaults to DEFAULT_PASSWD_DIR.)
   * @param string $passwdFile The filename of the managed htpasswd file. (Defaults to $reposName.)
   */
  public function __construct($reposName, array $adminNames, $passwdDir = null, $passwdFile = null)
  {
    if(empty($reposName))
      throw new InvalidArgumentException('No repository selected.');

    if(empty($passwdDir)) $passwdDir = DEFAULT_PASSWD_DIR;
    if(empty($passwdFile)) $passwdFile = $reposName;

    $passwdPath = $passwdDir . DIRECTORY_SEPARATOR . $passwdFile;
    if(!is_file($passwdPath))
      throw new Exception('The corresponding password file does not exist.');

    $this->reposName = $reposName;
    $this->passwdPath = $passwdPath;
    $this->adminNames = $adminNames;
    sort($this->adminNames);
    $this->loadUsers();
  }

  /**
   * Displays all information regarding the permission level and processes any submits.
   * In a classic usage only this method should be invoked.
   * @param string Name of the currently logged in user.
   */
  public function show($username)
  {
    ?>
    <h1><?php echo $this->reposName; ?> repository</h1>
    <?php

    $this->process($username);
    $this->showInfo($username);    

    if(!in_array($username, $this->adminNames))
    {
      $this->showPasswordForm($username);
    }
    else
    {
      $this->showModifyForm($username);
      $this->showAddForm($username);
      $this->showDeleteForm($username);
    }
  }

  /**
   * Displays repository information.
   * @param string $username Name of the currently logged in user.
   */
  public function showInfo($username)
  {
    ?>
    <h2>Repository information</h2>
    <p>
      <b>Repository master(s): </b>
      <?php

      for($i = 0; $i < count($this->adminNames); ++$i)
      {
        echo $this->adminNames[$i];
        if($i < count($this->adminNames) - 1)
          echo ', ';
      }

      ?>
    </p>
    <p>
      <b>Repository user(s): </b>
      <?php

      for($i = 0; $i < count($this->userNames); ++$i)
      {
        if($username == $this->userNames[$i]) echo '<u>';
        echo $this->userNames[$i];
        if($username == $this->userNames[$i]) echo '</u>';
        if($i < count($this->userNames) - 1)  echo ', ';
      }

      ?>
    </p>
    <?php
  }

  /**
   * Displays the password modification form for normal users.
   * @param string $username Name of the currently logged in user.
   */
  public function showPasswordForm($username)
  {
    ?>
    <h2>Modify password</h2>
    <form name="modify" method="post">
      <div><b>Username:</b></div>
      <div><?php echo $username; ?></div>

      <div><b>New password:</b></div>
      <div><input name="password" type="password"></div>

      <div><b>Confirm password:</b></div>
      <div><input name="confirmation" type="password"></div>

      <div><input name="modify-submit" type="submit" value="Submit"></div>
    </form>
    <?php
  }

  /**
   * Displays the password modification form for administrators.
   * @param string $username Name of the currently logged in user.
   */
  public function showModifyForm($username)
  {
    ?>
    <h2>Modify password</h2>
    <form name="modify" method="post">
      <div><b>Username:</b></div>
      <div>
        <select name="username">
          <?php
          foreach($this->userNames as $user)
          {
            echo '<option' . ($user == $username ? ' selected' : '') .  '>' . 
                 $user . '</option>' . PHP_EOL;
          }
          ?>
        </select>
      </div>

      <div><b>New password:</b></div>
      <div><input name="password" type="password"></div>

      <div><b>Confirm password:</b></div>
      <div><input name="confirmation" type="password"></div>

      <div><input name="modify-submit" type="submit" value="Submit"></div>
    </form>
    <?php
  }

  /**
   * Displays the user addition form.
   * @param string $username Name of the currently logged in user.
   */
  public function showAddForm($username)
  {
    ?>
    <h2>Add new user</h2>
    <form name="add" method="post">
      <div><b>Username:</b></div>
      <div><input name="username" type="text"></div>

      <div><b>New password:</b></div>
      <div><input name="password" type="password"></div>

      <div><b>Confirm password:</b></div>
      <div><input name="confirmation" type="password"></div>

      <div><input name="add-submit" type="submit" value="Submit"></div>
    </form>
    <?php
  }

  /**
   * Displays the user deletion form.
   * @param string $username Name of the currently logged in user.
   */
  public function showDeleteForm($username)
  {
    ?>
    <h2>Delete existing user</h2>
    <form name="delete" method="post">
      <div><b>Username:</b></div>
      <div>
        <select name="username">
          <?php
          foreach($this->userNames as $user)
          {
            echo '<option' . ($user == $username ? ' selected' : '') .  '>' . 
                 $user . '</option>' . PHP_EOL;
          }
          ?>
        </select>
      </div>

      <div><input name="delete-submit" type="submit" value="Submit"></div>
    </form>
    <?php
  }

  /**
   * Processes the possible form submits.
   * @param string $username Name of the currently logged in user.
   */
  public function process($username)
  {
    if(isset($_POST['modify-submit']) && isset($_POST['password']) && isset($_POST['confirmation']))
    {
      try
      {
        if(isset($_POST['username']) && !in_array($username, $this->adminNames))
          throw new Exception('Insufficient permissions.');
        $this->modifyPassword(isset($_POST['username']) ? $_POST['username'] : $username, $_POST['password'], $_POST['confirmation']);
      }
      catch(Exception $ex)
      {
        echo '<p><i>Fail: ' . $ex->getMessage() . '</i></p>';
      }
    }
    elseif(isset($_POST['add-submit']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['confirmation']))
    {
      try
      {
        if(!in_array($username, $this->adminNames))
          throw new Exception('Insufficient permissions.');
        $this->addUser($_POST['username'], $_POST['password'], $_POST['confirmation']);
      }
      catch(Exception $ex)
      {
        echo '<p><i>Fail: ' . $ex->getMessage() . '</i></p>';
      }
    }
    elseif(isset($_POST['delete-submit']) && isset($_POST['username']))
    {
      try
      {
        if(!in_array($username, $this->adminNames))
          throw new Exception('Insufficient permissions.');
        $this->deleteUser($_POST['username']);
      }
      catch(Exception $ex)
      {
        echo '<p><i>Fail: ' . $ex->getMessage() . '</i></p>';
      }
    }
  }

  /**
   * Modifies the password of an existing user.
   * @param string $username Username.
   * @param string $password Password.
   * @param string $confirmation Password confirmation.
   */
  private function modifyPassword($username, $password, $confirmation)
  {
    if(empty($username) || empty($password) || empty($confirmation))
      throw new InvalidArgumentException('All arguments must be given.');

    if($password != $confirmation)
      throw new Exception('The new password does not match with its confirmation.');

    if(!in_array($username, $this->userNames))
      throw new LogicException('User does not exist.');

    $permissions = file($this->passwdPath);
    for($i = 0; $i < count($permissions); ++$i)
      if($username == strstr($permissions[$i], ':', true))
      {
        $permissions[$i] = $username . ':' . crypt_apr1_md5($password) . PHP_EOL;
        file_put_contents($this->passwdPath, implode('', $permissions));
        echo '<p><i>Password successfully updated.</i></p>';
        return;
      }

    throw new LogicException('User not found.');
  }

  /**
   * Adds a new user to the repository.
   * @param string $username Username.
   * @param string $password Password.
   * @param string $confirmation Password confirmation.
   */
  private function addUser($username, $password, $confirmation)
  {
    if(empty($username) || empty($password) || empty($confirmation))
      throw new InvalidArgumentException('All arguments must be given.');

    if($password != $confirmation)
      throw new Exception('The new password does not match with its confirmation.');

    if(in_array($username, $this->userNames))
      throw new LogicException('User already exists.');
    
    $permission = $username . ':' . crypt_apr1_md5($password) . PHP_EOL;
    file_put_contents($this->passwdPath, $permission , FILE_APPEND);
    echo '<p><i>User successfully added.</i></p>';

    $this->loadUsers();
  }

  /**
   * Deletes an existing user.
   * @param string $username Username.
   */
  private function deleteUser($username)
  {
    if(empty($username))
      throw new InvalidArgumentException('All arguments must be given.');

    if(!in_array($username, $this->userNames))
      throw new LogicException('User does not exist.');
    
    $i = 0;
    $permissions = file($this->passwdPath);
    for(; $i < count($permissions); ++$i)
      if($username == strstr($permissions[$i], ':', true))
        break;

    if($i == count($permissions))
      throw new LogicException('User not found.');

    unset($permissions[$i]);
    file_put_contents($this->passwdPath, implode('', $permissions));
    echo '<p><i>User successfully deleted.</i></p>';

    $this->loadUsers();
  }

  /**
   * (Re)Loads the usernames into the current object from the managed htpasswd file.
   */
  private function loadUsers()
  {
    $this->userNames = array();
    $permissions = file($this->passwdPath);
    foreach ($permissions as $permission)
    {
      $userName = strstr($permission, ':', true);
      $this->userNames[] = $userName;
    }
    sort($this->userNames);
  }
}

/**
 * APR1-MD5 encryption method (windows compatible)
 * @param  string $plainpasswd Password.
 * @return string MD5 hash.
 * @link   https://www.virendrachandak.com/techtalk/using-php-create-passwords-for-htpasswd-file/
 * @author Virendra Chandak
 */
function crypt_apr1_md5($plainpasswd)
{
    $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
    $len = strlen($plainpasswd);
    $text = $plainpasswd.'$apr1$'.$salt;
    $bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));
    for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
    for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $plainpasswd{0}; }
    $bin = pack("H32", md5($text));
    for($i = 0; $i < 1000; $i++)
    {
        $new = ($i & 1) ? $plainpasswd : $bin;
        if ($i % 3) $new .= $salt;
        if ($i % 7) $new .= $plainpasswd;
        $new .= ($i & 1) ? $bin : $plainpasswd;
        $bin = pack("H32", md5($new));
    }
    $tmp = '';
    for ($i = 0; $i < 5; $i++)
    {
        $k = $i + 6;
        $j = $i + 12;
        if ($j == 16) $j = 5;
        $tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
    }
    $tmp = chr(0).chr(0).$bin[11].$tmp;
    $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
    "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
 
    return "$"."apr1"."$".$salt."$".$tmp;
}

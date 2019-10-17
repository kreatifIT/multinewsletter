<?php

/**
 * Benutzer des MultiNewsletters.
 */
class MultinewsletterUser {
	/**
	 * @var int Database ID
	 */
	var $id = 0;
	
	/**
	 * @var string Email address
	 */
	var $email = "";
	
	/**
	 * @var string Academic degree
	 */
	var $grad = "";
	
	/**
	 * @var string First name
	 */
	var $firstname = "";
	
	/**
	 * @var string Last name
	 */
	var $lastname = "";
	
	/**
	 * @var int Title 0 = male, 1 = female
	 */
	var $title = 0;
	
	/**
	 * @var int Redaxo language id
	 */
	var $clang_id = 0;
	
	/**
	 * @var int Status. 0, = inactive, 1 =  active, 2 = not verified
	 */
	var $status = 0;
	
	/**
	 * @var string[] Array with group ids
	 */
	var $group_ids = [];
	
	/**
	 * @var string Mailchimp ID
	 */
	var $mailchimp_id = "";

	/**
	 * @var string Create date (format: Y-m-d H:i:s)
	 */
	var $createdate = "";

	/**
	 * @var string Create IP Address
	 */
	var $createip = "";

	/**
	 * @var string Activation date (format: Y-m-d H:i:s)
	 */
	var $activationdate = "";

	/**
	 * @var string Activation IP Address
	 */
	var $activationip = "";

	/**
	 * @var string Activation Key
	 */
	var $activationkey = "";

	/**
	 * @var string Update date (format: Y-m-d H:i:s)
	 */
	var $updatedate = "";
	
	/**
	 * @var string Update IP Address
	 */
	var $updateip = "";

	/**
	 * @var string Type of subcription, "web", "import", "backend"
	 */
	var $subscriptiontype = "";
	
	/**
	 * @var int Has privacy policy been accepted? 1 = yes, 0 = no
	 */
	var $privacy_policy_accepted = 0;
	
    /**
     * Get user data from database
     * @param int $user_id user id
     */
    public function __construct($user_id) {
 		$query = "SELECT * FROM ". \rex::getTablePrefix() ."375_user WHERE id = ". $user_id;
		$result = \rex_sql::factory();
		$result->setQuery($query);

		if ($result->getRows() > 0) {
			$this->id = $result->getValue("id");
			$this->email = $result->getValue("email");
			$this->grad = $result->getValue("grad");
			$this->firstname = $result->getValue("firstname");
			$this->lastname = $result->getValue("lastname");
			$this->title = $result->getValue("title") == "" ? 0 : $result->getValue("title");
			$this->clang_id = $result->getValue("clang_id");
			$this->status = $result->getValue("status");
			$group_separator = strpos($result->getValue("group_ids"), '|') !== FALSE ? "|" : ",";
			$this->group_ids = preg_grep('/^\s*$/s', explode($group_separator, $result->getValue("group_ids")), PREG_GREP_INVERT);
			$this->mailchimp_id = $result->getValue("mailchimp_id");
			$this->createdate = $result->getValue("createdate");
			$this->createip = $result->getValue("createip");
			$this->activationdate = $result->getValue("activationdate");
			$this->activationip = $result->getValue("activationip");
			$this->activationkey = $result->getValue("activationkey");
			$this->updatedate = $result->getValue("updatedate");
			$this->updateip = $result->getValue("updateip");
			$this->subscriptiontype = $result->getValue("subscriptiontype");
			$this->privacy_policy_accepted = $result->getValue("privacy_policy_accepted");
		}
    }

    /**
     * Create a new user
     * @param string $email email address
     * @param int $title title (0 = male, 1 = female)
     * @param string $grad academic degree
     * @param string $firstname First name
     * @param string $lastname Last name
     * @param int $clang_id Redaxo clang id
     * @return MultinewsletterUser initialized user
     */
    public static function factory($email, $title, $grad, $firstname, $lastname, $clang_id) {
		$user = self::initByMail($email) ?: new self(0);

        $user->email = $email;
        $user->title = $title;
        $user->grad = $grad;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->clang_id = $clang_id;
        $user->status = 1;
        $user->createdate = date('Y-m-d H:i:s');
        $user->createip = $_SERVER['REMOTE_ADDR'];

        return $user;
    }

    /**
     * Activate user
     */
    public function activate() {
        $this->activationkey = '';
        $this->activationdate = date('Y-m-d H:i:s');
        $this->activationip = $_SERVER['REMOTE_ADDR'];
        $this->updatedate = date('Y-m-d H:i:s');
        $this->updateip = $_SERVER['REMOTE_ADDR'];
        $this->status = 1;
        $this->save();

        rex_extension::registerPoint(new rex_extension_point('multinewsletter.userActivated', $this));

        $this->sendAdminNoctificationMail("subscribe");
    }

    /**
     * Delete user
     */
    public function delete() {
        if (MultinewsletterMailchimp::isActive()) {
            $Mailchimp = MultinewsletterMailchimp::factory();

            try {
                foreach ($this->group_ids as $group_id) {
                    $group = new MultinewsletterGroup($group_id);

                    if (strlen($group->mailchimp_list_id)) {
                        $Mailchimp->unsubscribe($this, $group->mailchimp_list_id);
                    }
                }
            }
            catch (MultinewsletterMailchimpException $ex) {
            }
        }

		$sql = rex_sql::factory();
		$sql->setQuery("DELETE FROM ". \rex::getTablePrefix() ."375_user WHERE id = ". $this->id);
    }

	/**
	 * Get full name
	 * @return string Name
	 */
    public function getName() {
        return trim($this->firstname) .' '. trim($this->lastname);
    }

	/**
	 * Get archive id(s), that should be sent to user
	 * @param boolean $autosend_only If TRUE, only archive IDs with autosend 
	 * option are returned.
	 * @return int[] Array with Archive IDs that will be sent to user.
	 */
    public function getSendlistArchiveIDs($autosend_only = FALSE) {
		$archive_ids = [];
		
	    $result = rex_sql::factory();
		$result->setQuery("SELECT archive_id FROM ". rex::getTablePrefix() ."375_sendlist "
			. "WHERE user_id = ". $this->id
			.($autosend_only ? " AND autosend = 1" : ""));
		
        for ($i = 0; $result->getRows() > $i; $i++) {
            $archive_ids[] = $result->getValue('archive_id');
            $result->next();
        }
		
        return $archive_ids;
    }

    /**
     * Fetch user from database
     * @param string $email email address
     * @return MultinewsletterUser|boolean Initialized MultinewsletterUser object.
     */
    public static function initByMail($email) {
 		$query = "SELECT * FROM ". \rex::getTablePrefix() ."375_user WHERE email = '". trim($email) ."'";
		$result = \rex_sql::factory();
		$result->setQuery($query);

		if ($result->getRows() > 0) {
			return new MultinewsletterUser($result->getValue("id"));
		}
		return FALSE;

    }

    /**
     * Personalize activation mail string
     * @param string $content string to be personalized
     * @return string Personalized string
     */
    private function personalize($content) {
        $addon = rex_addon::get("multinewsletter");

        $content = str_replace("+++EMAIL+++", $this->email, stripslashes($content));
        $content = str_replace("+++GRAD+++", htmlspecialchars(stripslashes($this->grad), ENT_QUOTES), $content);
        $content = str_replace("+++LASTNAME+++", htmlspecialchars(stripslashes($this->lastname), ENT_QUOTES), $content);
        $content = str_replace("+++FIRSTNAME+++", htmlspecialchars(stripslashes($this->firstname), ENT_QUOTES), $content);
        $content = str_replace("+++TITLE+++", htmlspecialchars(stripslashes($addon->getConfig('lang_' . $this->clang_id . "_title_" . $this->title)), ENT_QUOTES), $content);
        $content = preg_replace('/ {2,}/', ' ', $content);

        $subscribe_link = (\rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : \rex::getServer())
			. trim(trim(rex_getUrl($addon->getConfig('link'), $this->clang_id, ['activationkey' => $this->activationkey, 'email' => rawurldecode($this->email)], '&'), "/"), "./");
        if (rex_addon::get('yrewrite')->isAvailable()) {
            // Use Yrewrite, support for Redaxo installations in subfolders: https://github.com/TobiasKrais/multinewsletter/issues/7
            $subscribe_link = \rex_yrewrite::getFullUrlByArticleId($addon->getConfig('link'), $this->clang_id, ['activationkey' => $this->activationkey, 'email' => rawurldecode($this->email)], '&');
        }
        return str_replace("+++AKTIVIERUNGSLINK+++", $subscribe_link, $content);
    }

    public function setValue($key, $value) {
        $this->{$key} = $value;
    }

    public function getValue($key) {
        return $this->{$key};
    }

    /**
     * Update user in database
	 * @return boolean TRUE if error occured
     */
	public function save() {
		$error = TRUE;

		$properties = [];

		if ($this->createdate == '') {
            $this->createdate = date('Y-m-d H:i:s');
        }
		if ($this->createip == '') {
            $this->createip = $_SERVER['REMOTE_ADDR'];
        }
        $this->updatedate = date('Y-m-d H:i:s');
        $this->updateip = $_SERVER['REMOTE_ADDR'];

		foreach (get_object_vars($this) as $key => $value) {

            if ($key == 'group_ids') {
                $value = "'|". implode('|', $value) ."|'";
            } elseif (is_array($value)) {
                $value = implode(',', $value);
            } elseif (is_numeric($value)) {
                $value = $value;
            } else {
                $value = "'". trim($value) ."'";
            }
            $properties[] = "{$key} = {$value}";
        }
        $query = \rex::getTablePrefix() ."375_user SET ". implode(', ', $properties);

		if($this->id == 0) {
			$query = "INSERT INTO ". $query;
		}
		else {
			$query = "UPDATE ". $query ." WHERE id = ". $this->id;
		}
		$result = \rex_sql::factory();
		$result->setQuery($query);
		if($this->id == 0) {
			$this->id = $result->getLastId();
			$error = !$result->hasError();
		}

		// Don't forget Mailchimp
        if (MultinewsletterMailchimp::isActive()) {
            $Mailchimp = MultinewsletterMailchimp::factory();
            $_status = $this->status == 2 ? 'unsubscribed' : ($this->status == 1 ? 'subscribed' : 'pending');

            try {
                foreach ($this->group_ids as $group_id) {
                    $group = new MultinewsletterGroup($group_id);

                    if (strlen($group->mailchimp_list_id)) {
                        $result = $Mailchimp->addUserToList($this, $group->mailchimp_list_id, $_status);
                        $this->mailchimp_id = $result['id'];
                    }
                }
            }
            catch (MultinewsletterMailchimpException $ex) {
            }
        }
		
		return $error;
    }

    /**
     * Send activation mail
     * @param string $sender_mail Sender email addresss
     * @param string $sender_name Sender name
     * @param string $subject Mail subject
     * @param string $body Mail content
     * @return boolean TRUE if successful, otherwise FALSE
     */
    public function sendActivationMail($sender_mail, $sender_name, $subject, $body) {
        if (!empty($body) && strlen($this->email) && filter_var($sender_mail, FILTER_VALIDATE_EMAIL) !== false) {
            $mail = new rex_mailer();
            $mail->IsHTML(true);
            $mail->CharSet  = "utf-8";
            $mail->From = $sender_mail;
            $mail->FromName = $sender_name;
            $mail->Sender = $sender_mail;
            $mail->AddAddress($this->email, $this->getName());

            $mail->Subject = $this->personalize($subject);
            $mail->Body    = rex_extension::registerPoint(new rex_extension_point('multinewsletter.preSend', $this->personalize($body), [
                'mail' => $mail,
                'user' => $this,
            ]));
            return $mail->Send();
        }
        else {
            return FALSE;
        }
    }

    /**
     * Send admin mail with hint, that user status changed
     * @param string $type Either "subscribe" or "unsubscribe"
     * @return boolean TRUE if successful, otherwise FALSE
     */
    public function sendAdminNoctificationMail($type) {
        $addon = rex_addon::get('multinewsletter');

        if (filter_var($addon->getConfig('subscribe_meldung_email'), FILTER_VALIDATE_EMAIL) !== FALSE) {
            $mail = new rex_mailer();
            $mail->IsHTML(true);
            $mail->CharSet  = "utf-8";
            $mail->From = $addon->getConfig('sender');
            $mail->FromName = $addon->getConfig('lang_' . $this->clang_id . "_sendername");
            $mail->Sender = $addon->getConfig('sender');

            $mail->AddAddress($addon->getConfig('subscribe_meldung_email'));

            if ($type == "subscribe") {
                $mail->Subject = "Neue Anmeldung zum Newsletter";
                $mail->Body    = "Neue Anmeldung zum Newsletter: " . $this->email;
            }
            else {
                $mail->Subject = "Abmeldung vom Newsletter";
                $mail->Body    = "Abmeldung vom Newsletter: " . $this->email;
            }
            return $mail->Send();
        }
        else {
            return FALSE;
        }
    }

    /**
     * Unsubcribe user
     */
    public function unsubscribe($action = "delete") {
        if ($action == "delete") {
            $this->delete();
        }
        else {
            // $action = "status_unsubscribed"
            $this->status = 2;
            $this->save();
        }

        $this->sendAdminNoctificationMail("unsubscribe");
    }
}

<?php

class Password extends Controller
{
	// private $email;
	public function __construct()
	{
		// $this->email = $_SESSION['email'];
		$this->user_model = $this->model('User');
		$this->account_model = $this->model('Account');
		$this->reset_pass_model = $this->model('ResetPassword');
		$this->validation = $this->service('validation');
	}

	
	public function change_password()
	{
		$data = [
			'selector' => '',
			'validator' => '',
			'password' => '',
			'confirm_password' => '',
			'password_error' => '',
			'confirm_pass_error' => '',
			'error' => ''
		];

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$data = [
				'selector' => trim($_POST['selector']),
				'validator' => trim($_POST['validator']),
				'password' => trim($_POST['password']),
				'confirm_password' => trim($_POST['confirm_password']),
				'password_error' => '',
				'confirm_pass_error' => '',
				'error' => ''
			];

			// Validate password
			$validate_password = $this->validation->validatePassword($data['password']);
			if ($validate_password != 'true') {
				$this->close([
					'data_err' => $validate_password
				]);
			}

			// Validate confirm password
			$validate_confirm_pass = $this->validation->validateConfirmPass($data['password'], $data['confirm_password']);
			if ($validate_confirm_pass != 'true') {
				$this->close([
					'data_err' => $validate_confirm_pass
				]);
			}

			if ($validate_password && $validate_confirm_pass) {
				$current_date = date('U');

				$row = $this->reset_pass_model->checkExpire($data['selector'], $current_date);

				if (!$row) {
					$data['error'] = 'Xin lỗi, đường dẫn đã hết hiệu lực.';
					$this->getJsonData($data);
				}
				$token_bin = hex2bin($data['validator']);
				$token_check = password_verify($token_bin, $row->reset_token);

				if (!$token_check) {
					$data['error'] = 'Bạn cần gửi lại yêu cầu đổi mật khẩu.';
					$this->getJsonData($data);
				}
				$token_email = $row->reset_email;
				$user_row = $this->user_model->findUserByEmail($token_email);
				if (!$user_row) {
					$data['error'] = 'Có lỗi đã xảy ra';
					$this->getJsonData($data);
				}
				$account = $this->account_model->getAccountByID($user_row->account_id);
				$pass_hash = password_hash($data['password'], PASSWORD_DEFAULT);
				$id = $account->account_id;
				if (!$this->account_model->changePassword($id, $pass_hash)) {
					$data['error'] = 'Có lỗi đã xảy ra';
					$this->getJsonData($data);
				}
				if (!$this->reset_pass_model->deleteResetEmail($token_email)) {
					$data['error'] = 'Có lỗi đã xảy ra';
					$this->getJsonData($data);
				}
				$this->close([
					'data_err' => '',
					'page_err' => ''
				]);
			}
		}
		$this->view('authentication/change.password', $data);
	}

	public function getJsonData($data)
	{
		// if (!empty($data['error'])) {
		$this->close([
			'data_err' => '',
			'page_err' => $data['error'] . '<br/>',
		]);
		// }
	}

	public function update_password()
	{
		$data = [
			'old_password' => '',
			'new_password' => '',
			'conf_new_password' => '',
			'old_pass_error' => '',
			'new_pass_error' => '',
			'conf_new_pass_error' => ''
		];

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$user = $_SESSION['username'];
			$data = [
				'old_password' => $_POST['old_password'],
				'new_password' => $_POST['new_password'],
				'conf_new_password' => $_POST['conf_new_password'],
				'old_pass_error' => '',
				'new_pass_error' => '',
				'conf_new_pass_error' => ''
			];
			$account = $this->account_model->getAccountByUser($user);

			// Validate old password input
			if (empty($data['old_password'])) {
				$data['old_pass_error'] = 'Xin hãy nhập mật khẩu hiện tại';
			} elseif (!password_verify($data['old_password'], $account->password)) {
				$data['old_pass_error'] = 'Sai mật khẩu hiện tại';
			}

			// Validate new password input
			if (empty($data['new_password'])) {
				$data['new_pass_error'] = 'Xin hãy nhập mật khẩu mới';
			} elseif (!preg_match(pass_validation, $data['new_password'])) {
				$data['new_pass_error'] = "Mật khẩu phải chứa ít nhất 8 kí tự, có ít nhất
				1 chữ cái in hoa và 1 số";
			}

			// Validate confirm new password input
			if (empty($data['conf_new_password'])) {
				$data['conf_new_password'] = 'Xin hãy nhập mật khẩu xác thực mới';
			} elseif (!(strcmp($data['new_password'], $data['conf_new_password']) == 0)) {
				$data['conf_new_pass_error'] = 'Mật khẩu xác thực và mật khẩu mới không trùng khớp';
			}

			// If no error, update password
			if (
				empty($data['old_pass_error']) && empty($data['new_pass_error'])
				&& empty($data['conf_new_pass_error'])
			) {
				$data['new_password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
				$this->account_model->changePassword($account->account_id, $data['new_password']);
			}

			// Send data to view
			$response = '';
			if (!empty($data['old_pass_error'])) {
				$response .= $data['old_pass_error'] . '<br/>';
			} elseif (!empty($data['new_pass_error'])) {
				$response .= $data['new_pass_error'] . '<br/>';
			} elseif (!empty($data['conf_new_pass_error'])) {
				$response .= $data['conf_new_pass_error'] . '<br/>';
			}

			$this->close([
				'success' => false,
				'msg' => $response
			]);
		}
	}
}

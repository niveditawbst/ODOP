<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tactconnect\PassEncryption\Controller\Account;


/**
 * Class ResetPasswordPost
 *
 * @package Magento\Customer\Controller\Account
 */
class ResetPasswordPost extends \Magento\Customer\Controller\Account\ResetPasswordPost
{
    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');
        $salt = $this->session->getFrontendResetPasswordEncryptionKey();
        $key = pack("H*", $salt.$salt);
        $iv = pack("H*", $salt);
        $password = base64_decode($password);
        $password = openssl_decrypt($password , 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $passwordConfirmation = base64_decode($passwordConfirmation);
        $passwordConfirmation = openssl_decrypt($passwordConfirmation , 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        echo $password.'::'.$passwordConfirmation;exit;
        $this->session->unsFrontendResetPasswordEncryptionKey();
        if ($password !== $passwordConfirmation) {
            $this->messageManager->addError(__("New Password and Confirm New Password values didn't match."));
            $resultRedirect->setPath('*/*/createPassword', ['token' => $resetPasswordToken]);

            return $resultRedirect;
        }
        if (iconv_strlen($password) <= 0) {
            $this->messageManager->addError(__('Please enter a new password.'));
            $resultRedirect->setPath('*/*/createPassword', ['token' => $resetPasswordToken]);

            return $resultRedirect;
        }

        try {
            $this->accountManagement->resetPassword(
                null,
                $resetPasswordToken,
                $password
            );
            $this->session->unsRpToken();
            $this->messageManager->addSuccess(__('You updated your password.'));
            $resultRedirect->setPath('*/*/login');

            return $resultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($error->getMessage());
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Something went wrong while saving the new password.'));
        }
        $resultRedirect->setPath('*/*/createPassword', ['token' => $resetPasswordToken]);

        return $resultRedirect;
    }
}



PayFast module for OpenCart

PayFast OpenCart Module v1.0.1 for OpenCart v4.0.2.2
-------------------------------------------------------
Copyright (c) 2023 PayFast (Pty) Ltd
You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.

1. Compress the contents of the "payfast" folder into a single zip. When doing so, make sure to highlight the contents of the folder and not the folder itself. Rename the zip to payfast.ocmod.zip.
2. Login to the admin section of your OpenCart installation.
3. Navigate to the Installer under Extensions.
4. Upload the Compressed Payfast Folder (payfast.ocmod.zip).
5. Navigate to the Extensions > Payments page.
6. Scroll down to the PayFast payment method and click the “Install” button to install the module.
7. Once the module is installed, click on “Edit” button.
8. The PayFast options will then be shown, select the payment status for “completed”, “failed” and “pending” payments, select the sandbox mode, enable the payment module and click “Save”.
9. The module is now ready to be tested with the Sandbox. To test with the sandbox, use the following login credentials when redirected to the PayFast site:
- Username: sbtu01@payfast.co.za
- Password: clientpass

How can I test that it is working correctly?
If you followed the installation instructions above, the module is in “test” mode and you can test it by purchasing from your site as a buyer normally would. You will be redirected to PayFast for payment and can login with the user account detailed above and make payment using the balance in their wallet.

You will not be able to directly “test” a credit card, Instant EFT or Ukash payment in the sandbox, but you don”t really need to. The inputs to and outputs from PayFast are exactly the same, no matter which payment method is used, so using the wallet of the test user will give you exactly the same results as if you had used another payment method.

I'm ready to go live! What do I do?
In order to make the module “LIVE”, follow the instructions below:

1. Login to the admin section of your OpenCart system
2. Navigate to the Extensions > Payments page
3. Under PayFast.co.za, click on the “Edit” link
4. n the PayFast Settings block, use the following settings:
5. Set Sandbox Mode = “No”
6. Merchant ID = <Login to PayFast -> Integration Page>
7. Merchant Key = <Login to PayFast -> Integration Page>
8. Click Save

******************************************************************************

    Please see the URL below for all information concerning this module:

                 https://payfast.io/integration/shopping-carts/opencart/

******************************************************************************

<?php # create_pass.php

// Set the page title and include the HTML header.
include ('./header.php');
if (isset($_POST['submit']))
{
        // Check for a name.
        if (strlen($_POST['name']) > 0)
        {
                $name = TRUE;
        }
        else
        {
                $name = FALSE;
                echo '<p>You forgot to enter your name!</p>';
        }

        // Check for an e-mail address.
        if (strlen($_POST['email'] > 0)
        {
                $email = TRUE;
        }
        else
        {
                $email = FALSE;
                echo '<p>You forgot to enter your email!</p>';
        }

        // Check for a username
        if (strlen($_POST['username']) > 0)
        {
                $username = TRUE;
        }
        else
        {
                $username = FALSE;
                echo '<p>You forgot to enter your username!</p>';
        }

        // Check for a password and match against the confirmed password.
        if(strlen($_POST['password1'] > 0 )
        {
                if($_POST['password1'] == $_POST['password2'])
                {
                        $password = TRUE;
                }
                else
                {
                        $password = FALSE;
                        echo '<p>Your password did not match the confirmed
                              password!</p>';
                }
        }
        else
        {
                $password = FALSE;
                echo '<p>You forgot to enter your password!</p>';
        }

        if ($name && $email && $username && $password)
        { // If it's all good
                // Register the user.
                echo '<p>You are now registered.</p>';
        }
        else
        { // It's NOT all good
                echo '<p>Please go back and try again.</p>';
        }
}
else // Display the form.
{
?>
        <form action = "<?php echo $_SERVER['PHP_SELF'] ?>" method = "post">
        <fieldset><legend>Enter your information in the form below:</legend>

        <p><b>Name:</b><input type = "text" name = "name" size = "20"
                        maxlength = "40" /> </p>

        <p><b>E-mail Address:</b><input type = "text" name = "email" size = "40"
                        maxlength = "60" /> </p>

        <p><b>Username:</b><input type = "text" name = "username" size = "20"
                        maxlength = "40" /> </p>

        <p><b>Password:</b><input type = "password" name = "password1"
                        size = "20" maxlength = "40" /> </p>

        <p><b>Confirm Password:</b><input type = "password" name = "password2"
                        size = "20" maxlength = "40" /> </p>

        <div align = "center"><input type = "submit" name = "submit"
                        value = "Submit Information" />
        </div>

        </form><!-- End of Form -->

        <?php
} // End of the main SUBMIT conditional.

include ('./footer.php');
?>




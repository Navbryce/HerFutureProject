<?php
    $name = $_POST["name"];
    $email = $_POST["email"];
    $design = $_POST["design"];
    $paymentMethod = $_POST["paymentOption"];
    $captcha_response = $_POST["g-recaptcha-response"];
    $arrayResponse;
    $arrayResponse["debug"] = "Variables Set. Starting POST to google...";

    /*Verifies Captcha*/
    $postURL = "https://www.google.com/recaptcha/api/siteverify";
    $method = "POST";
    $postData = array(
        "secret" => "6Ld-uCgUAAAAAHXDZjbqBdQXvBmbFgh0lFOXzzHc", //Google secret key. Don't share.
        "response" => $captcha_response
    );
    $postOptions = array(
        'http' => array(
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'method' => $method,
            'content' => http_build_query($postData)
        )
    );
    $stream = stream_context_create($postOptions);
    $result = file_get_contents($postURL, false, $stream);
    if($result){
        $arrayResponse["debug"]= $arrayResponse["debug"].("Post successful. Verifying Captcha...");
        $googleResponse = json_decode($result, true);
        if($googleResponse["success"]){
            $arrayResponse["debug"] = $arrayResponse["debug"].("Captcha verification successful. Starting email.");

            $arrayResponse["debug"]=$arrayResponse["debug"].("Creating email text...");
            $message="<b>Name:</b> ".$name."<br><b>Email</b>: ".$email."<br><b>Designs:</b> ";

            for($designCounter=0; $designCounter<count($design); $designCounter++){
                if($designCounter>0){
                    if($designCounter==count($design)-1){ //Last design in list
                        $message.=" and ";
                    }else{
                        $message.=", ";
                    }
                }
                $message.=$design[$designCounter];

            }

            $message.="<br><b>Payment Method</b>: ".$paymentMethod;
            $arrayResponse["debug"]=$arrayResponse["debug"].("E-mail: ".$message);
            require './phpmailer/PHPMailerAutoload.php'; //Loads in the phpmailer dependency
            $mail = new PHPMailer;
            $mail->addAddress('juliehs.wang@gmail.com', 'Julie Wang');
            $mail->setFrom($email, $name);  ;
            $mail->isHTML(true);

            $mail->Subject = $name."'s Print Order";
            $mail->Body    = $message;

            if($mail->send()) {
                $arrayResponse["debug"]=$arrayResponse["debug"].'Message has been sent';
                $arrayResponse["success"] = "Thank you, your order has been submitted. You will now be redirected to the home page.";

            } else {
                $arrayResponse["debug"]=$arrayResponse["debug"].'Message could not be sent.';
                $arrayResponse["debug"]=$arrayResponse["debug"].'Mailer Error: ' . $mail->ErrorInfo;
            }

        }else{
            $arrayResponse["debug"]=$arrayResponse["debug"].("Captcha verfication not successful");
            $arrayResponse["error"] = "Your Captcha failed to verify. Please try redoing the captcha. Then, resubmit the order.";
        }
    }else{
        $arrayResponse["debug"]=$arrayResponse["debug"].("The post to google was not successful, and the form was not submitted.");
    }

    echo json_encode($arrayResponse);


?>

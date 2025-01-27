<?php 
    include 'php/config.php';// including the database connection
    session_start();
    $user_id = $_SESSION['user_id'];

    $get_user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
    if(!isset($user_id)){
        header('location: login.php');
    }

    // Fetch user details
    $select = mysqli_query($conn, "SELECT * FROM user_form WHERE user_id = '$get_user_id' ");
    if(mysqli_num_rows($select) > 0){
        $row = mysqli_fetch_assoc($select);
    }

    // Fetch chat messages
    $messages_sql = "SELECT * FROM messages 
                     LEFT JOIN user_form ON user_form.user_id = messages.outgoing_msg_id
                     WHERE (outgoing_msg_id = {$user_id} AND incoming_msg_id = {$get_user_id})
                     OR (outgoing_msg_id = {$get_user_id} AND incoming_msg_id = {$user_id})
                     ORDER BY msg_id DESC";  // Messages ordered by message ID in descending order
    $messages_query = mysqli_query($conn, $messages_sql);

    // Handle message sending
    if (isset($_POST['send_btn'])) {
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $incoming_id = $_POST['incoming_id'];

        if (!empty($message)) {
            $insert_message = mysqli_query($conn, "INSERT INTO messages (outgoing_msg_id, incoming_msg_id, msg) 
                                                   VALUES ({$user_id}, {$incoming_id}, '{$message}')");
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Chat Area</title>
</head>
<body>
    <div class="container">
        <section class="chat-area">
            <header>
                <a href="home.php" class="back-icon"><img src="images/arrow.svg" alt=""></a>
                <img src="uploaded_img/<?php echo $row['img'] ?>" alt="">
                <div class="details">
                    <span><?php echo $row['name'] ?></span>
                    <p><?php echo $row['status'] ?></p>
                </div>
            </header>
            <div class="chat-box">
                <?php
                    if(mysqli_num_rows($messages_query) > 0){
                        while($message = mysqli_fetch_assoc($messages_query)){
                            if($message['outgoing_msg_id'] === $user_id){
                                // For outgoing message
                                echo '<div class="chat outgoing">
                                        <div class="details">
                                            <p>'.$message['msg'].'</p>
                                        </div>
                                      </div>';
                            } else {
                                // For incoming message
                                echo '<div class="chat incoming">
                                        <img src="uploaded_img/'.$message['img'].'" alt="">
                                        <div class="details">
                                            <p>'.$message['msg'].'</p>
                                        </div>
                                      </div>';
                            }
                        }
                    } else {
                        echo '<div class="text">
                                <img src="uploaded_img/default-avatar.png" alt="">
                                <span>No messages are available. Once you send a message, it will appear here.</span>
                              </div>';
                    }
                ?>
            </div>
            <form action="" class="typing-area" method="POST">
                <input type="text" name="incoming_id" value="<?php echo $get_user_id ?>" class="incoming_id" hidden>
                <input type="text" name="message" class="input-field" placeholder="Type a message here....">
                <button class="image"><img src="images/camera.svg" alt=""></button>
                <input type="file" name="send_image" accept="image/*" class="upload_img" hidden>
                <button class="send_btn" type="submit" name="send_btn"><img src="images/send.svg" alt=""></button>
            </form>
        </section>
    </div>

    <script src="js/chat.js"></script>
</body>
</html>
<?php
session_start();
include 'header.php';
?>


<body class="dnp-bg-primary">
    <!--
    <div class="jumbotron text-center" style="margin-bottom:0">
        <h1>Hello, world!</h1>
    </div>
 -->


<?php if (isset($_SESSION['userid']) && $_SESSION['userid']) {?>
    <!-- big outer container -->
    <div class="container px-10 ">

        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-sm navbar-dark dnp-bg-primary sticky-top px-1">



            <!-- open contacts modal panel -->
            <a class="nav-link" id="showContacts" href="#contactsPanel" data-toggle="modal">
                <span class="text-light fas fa-user-friends"></span>
            </a>

            <a class="navbar-brand" href="#"><span class="pl-5 fas fa-comment"></span> Chat</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main_nav"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- main_nav -->
            <div class="collapse navbar-collapse" id="main_nav">

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#contactsPanel" data-toggle="modal">
                            <span class="fas fa-user-friends"></span>
                            Contacts
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href=logout.php>
                            <span class="fas fa-power-off"></span>
                            Log Off
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link  dropdown-toggle" href="#" data-toggle="dropdown"> Status </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a class="dropdown-item" href="#"> <span class="fas fa-check-circle">
                                    </span>
                                    Available</a></li>
                            <li><a class="dropdown-item" href="#"> <span class="fas fa-times-circle">
                                    </span>
                                    Busy </a></li>
                            <li><a class="dropdown-item" href="#"> <span class="far fa-clock"></span>
                                    Away</a></li>
                            <li class="dropdown-divider"></li>

                        </ul>
                    </li>

                </ul>

            </div> <!-- end main_nav navbar-collapse.// -->
        </nav>

	    <?php
include 'Chat.php';
    $chat = new Chat();
    $loggedUser = $chat->getUserDetails($_SESSION['userid']);
    $currentSession = '';
    $loggedUserName = '';
    foreach ($loggedUser as $user) {
        $currentSession = $user['current_session'];
        $loggedUserName = $user['username'];
        $userPic = $user['avatar'];

        // dnp trying to set some "variables" that javascript can use
        // loggedUserName
        // loggedUserid
        // toUserName
        // toUserId
        echo '<span id="user_data" '
            . 'data-loggedusername="' . $loggedUserName . '"'
            . 'data-loggeduserid="' . $_SESSION['userid'] . '"'
            . 'data-currentsession="' . $user['current_session'] . '"'
            . 'data-touserid=" "'
            . 'data-tousername=" "'
            . '></span>';

    }
    ?>

        <!-- https://jsfiddle.net/5qeyju7v/ -->
        <!-- Contacts modal panel -->
        <!-- class="modal fade animate" -->
        <div id="contactsPanel" class="modal " tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content ">

                    <div class="modal-header">

                        <button type="button" class="pt-1 mr-auto align-baseline" style="padding: 0; border: none; background: none;"
                            data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="fas fa-chevron-left"></span>
                        </button>



                        <button type="button" class="ml-auto btn btn-default" data-dismiss="modal">


                            <div class="p-1 align-middle float-right"> <h3><?php echo $loggedUserName; ?>
                            </div>
                            <div>
                             <?php echo '<img id="profile-img" src="userpics/' . $userPic . '" class="online px-0 align-middle float-right rounded-circle" alt="" style="width: 25%">'; ?>
                             </h3>
                            </div>
                        </button>
                    </div>

                    <div class="modal-body dnp-modal">
                        <!-- contact list -->
                        <div class="  text-dark pt-1 pl-1">

                    <?php
echo '<ul class="contacts">';
    $chatUsers = $chat->chatUsers($_SESSION['userid']);
    foreach ($chatUsers as $user) {
        $status = 'offline';
        if ($user['online']) {
            $status = 'online';
        } else { $status = 'offline';}
        $activeUser = '';
        if ($user['userid'] == $currentSession) {
            $activeUser = "active";
        }
        $lastActivity = $chat->getUsersLastConversationDate($_SESSION['userid'], $user['userid']);
        $lastMessage = $chat->getUsersLastMessage($_SESSION['userid'], $user['userid']);

        //$lastActivity = $chat->getUserLastActivity($user['userid']);

        echo '<li id="' . $user['userid'] . '" class="left clearfix contact ' . $activeUser . '" data-touserid="' . $user['userid'] . '" data-tousername="' . $user['username'] . '">';
        echo ' <button type="button" data-dismiss="modal" class="text-left btn-block"';
        echo ' style="padding: 0; border: none; background: none;">';

        // contact image
        //echo '<span class="float-left">';
        echo '<img width="50px" height="50px" 25px, src="userpics/' . $user['avatar'] . '" alt="" class="rounded-circle float-left">';
        //echo "</span>";

        // TBD contact on-line status
        echo '<span id="status_' . $user['userid'] . '" class="float-left contact-status ' . $status . '"></span>';

        // contact name
        echo '<div class="contacts-body clearfix pr-1">';
        // echo '<div class="header">';
        echo '<strong class="primary-font">' . $user['username'] . '</strong>';

        // contact un-read message count
        echo '<span id="unread_' . $user['userid'] . '" class="badge badge-pill badge-danger"  >' . $chat->getUnreadMessageCount($user['userid'], $_SESSION['userid']) . '</span>';

        // contact is typing
        echo '<small class="float-right text-dark"><span id="xisTyping_' . $user['userid'] . '" class="isTyping"></span></small>';

        //  contact last activity
        echo '<small class="float-right text-dark">';
        echo '<span class="far fa-clock"></span> ' . $lastActivity;
        echo '</small>';

        //  contact last message text
        echo '<p class="text-dark " style="font-size: .75rem">';
        echo $lastMessage;
        echo '</p>';

        // echo '</div>'; // end header
        echo '</div>'; // end contacts-body
        echo '</button>';
        echo '</li>';
    }
    echo '</ul>';
    ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                    </div>
                </div>
            </div>
        </div>
        <!-- end Contacts modal panel -->

        <div id="right2" class="container">
            <div class="row ">

                <!-- Right Column -->
                <div id="right" class="col-sm-8 p-0 dnp-bg-screen ">

                    <!-- Header area -->
                    <div class="col-lg-12 dnp-bg-screen border board-bottom-1 pl-2 pt-1" style=" height:100px; ">
                        <div id="userSection"  > </div>

                    </div>

                    <!-- Chat area -->
                    <div  class="col-lg-12 chatbody ">

                        <!-- all conversation messages are generated and html formated from the php code -->
                        <div id="conversationSection"> </div>
                    </div>


                    <!-- https://stackoverflow.com/questions/39784351/bootstrap-4-how-to-make-100-width-search-input-in-navbar -->
                    <!-- As of Bootstrap 4 the Navbar is flexbox so creating a full-width search input is easier. You can simply use w-100 and d-inline utility classes: -->
                    <!-- Send Message area -->
                    <div class="dnp-bg-screen container p-2" style="height:73px;">
                    <div id="isTyping" a="_<?php echo $user['userid']; ?>"></div>
                        <!-- <xform class="  pr-2 my-auto d-inline w-100 chatMessageForm" id="chatMessageForm<?php echo $currentSession; ?>"> -->
                            <div class="input-group message-input">
                                <input type="text" class=" form-control chatMessage message-input" id="chatMessage<?php echo $currentSession; ?>" placeholder="...">
                                <span class="input-group-append pr-3">
                                    <button class="btn btn-outline-dark chatButton chatMessageButton" type="button chatButton" id="chatButton<?php echo $currentSession; ?>">Send</button>
                                </span>
                            </div>
                        <!-- </xform> -->
                    </div>


                    <!-- <form class="mx-2 my-auto d-inline w-100">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="...">
                        <span class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button">GO</button>
                        </span>
                    </div>
                </form> -->

                    <!-- <div class="dnp-bg-header3" style="height:53px;">
                <div class="input-group p-2">
                    <input id="btn-input" type="text" class="form-control input-sm"
                        placeholder="Type your message here...">
                    <span class="input-group-btn p-1 pl-2 pr-3">
                        <button class="btn dnp-bg-secondary btn-sm" id="btn-chat">
                            Send</button>
                    </span>
                </div>
            </div> -->

                </div>

            </div> <!-- end right col -->
        </div> <!-- end row -->

    </div><!-- end of outside div -->

<?php } else {?>
		<br>
		<br>
        <script> window.location.replace("login.php");
        </script>
		<strong><a href="login.php"><h3>Chat Login</h3></a></strong>
	<?php }?>

<?php include 'footer.php';?>



    <script src="js/chat.js"></script>


    <script>
        // https://stackoverflow.com/questions/48851109/animate-css-on-bootstrap-4-modal
        // Different effects for showing and closing modal
        let fadeIn = 'dnp-animate-left';
        let fadeOut = 'dnp-animate-left-out';

        // On show
        $('#contactsPanel').on('show.bs.modal', function () {
            $(this).removeClass(fadeOut);
            $(this).addClass(fadeIn);
        });

        // On closing
        $('#contactsPanel').on('hide.bs.modal', function (e) {
            let $this = $(this);

            // Check whether the fade in class still exists in this modal
            // if this class is still exists prevent this modal
            // to close immediately by setting up timeout, and replace
            // the fade in class with fade out class.
            if ($this.hasClass(fadeIn)) {
                $this.removeClass(fadeIn);
                $this.addClass(fadeOut);
                e.preventDefault();

                setTimeout(function () {
                    $this.modal('hide');
                }, 395); // the default delays from animate.css is 1s
            }
        });
    </script>

</body>

</html>
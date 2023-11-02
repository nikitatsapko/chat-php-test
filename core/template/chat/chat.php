<div class="chat_block">
    <div class="messages_block">

    </div>
    <div class="input_block" bis_skin_checked="1">
        <input id="message" class="message" value="" placeholder="Введите сообщение..." type="text">
        <button class="submit_message" id="submit_message">отправить</button>
    </div>
</div>

<script>
    document.getElementById('message').disabled = true;
    document.getElementById('submit_message').disabled = true;

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // sending the message to the socket
    function sendMessage(message) {
        socket.send(message);
    }

    // parsing to JSON
    function parseMessage(message) {
        var msg = {type: "", sender: "", text: ""};
        try {
            msg = JSON.parse(message);
        }
        catch(e) {
            return false;
        }
        return msg;
    }

    // adding a message into the chatbox
    // (selfmess) 0 - stranger msg, 1 - sender msg
    function appendMessage(message, selfmess) {
        var parsedMsg;
        var msgContainer = document.querySelector(".messages_block");
        if (parsedMsg = parseMessage(message)) {
            console.log('appending message');
            console.log(parsedMsg);

            var msgElem, senderElem, textElem;
            var sender, text;

            msgElem = document.createElement("div");
            msgElem.setAttribute("id", "msg_items");
            msgElem.setAttribute("id", parsedMsg.idmess);
            if (!selfmess) {
                if (parsedMsg.sender == "<?php echo $_user['login'] ?>")
                    selfmess = 1;
                else
                    selfmess = 0;
            }

            msgElem.classList.add(selfmess ? 'my_message' : 'someone_message');
            msgElem.classList.add('msg_item');
            textElem = document.createElement("p");
            textElem.classList.add("msg-text");
            text = document.createTextNode(parsedMsg.sender + ": " + parsedMsg.text);
            msgElem.setAttribute("author", parsedMsg.sender);
            textElem.appendChild(text);
            msgElem.appendChild(textElem);
            msgContainer.appendChild(msgElem);

            // checking mod & admin permissions to add a moderation panel
            <?php if($_user['user_type'] > 0) echo '
            msgElem.setAttribute("oncontextmenu", "showModerationPanel(this);return false;");
            msgElem.innerHTML += `
                    <section id="dropdown-${parsedMsg.idmess}" class="dropdown dropdown-content">';
                if($_user['user_type'] > 1 ) echo '
                        <a href="/admin/users/${parsedMsg.uid}"><button>Перейти к пользователю</a></button> 
                        ';
                if($_user['user_type'] > 0) echo '
                        <button onclick="delete_message($(this).closest('."'div'".').attr('."'id'".'));">Удалить сообщение</button>
                        <button onclick="ban($(this).closest('."'div'".').attr('."'id'".'));">Заблокировать пользователя</button>
                    </section>
                `;
            ';
            ?>

            // auto-scrolling chat box
            $('.messages_block').animate({scrollTop: document.body.scrollHeight},"fast");
        }
    }

    // main trigger of sending a message in the socket
    function setup() {
        var sender = '';
        var msgForm = document.querySelector('form#message_form');

        function msgFormSubmit() {
            var msgField, msgText, msg;
            msgField = document.getElementById('message');
            msgText = msgField.value;
            if (msgText.trim() == "") return;
            msg = {
                type: "normal",
                sender: "<?php echo $_user['login'] ?>",
                text: msgText
            };
            msg = JSON.stringify(msg);
            sendMessage(msg);
            msgField.value = '';
        }

        document.addEventListener('keypress', function(event) {
            if(arguments[0].code == "Enter" || arguments[0].code == "NumpadEnter") {
                msgFormSubmit();
            }
        });

        document.getElementById('submit_message').addEventListener('click', msgFormSubmit);
    }

    // creating the socket connection
    let socket = new WebSocket("ws://146.59.73.243:8920");

    // checking of the connection to the socket
    var socketOpen = (e) => {
        console.log("connected to the socket");
        var msg = {
            type: 'join',
            sender: 'test',
            text: 'connected to the chat server',
            session: getCookie("usid"),
        }
        document.getElementById('message').disabled = false;
        document.getElementById('submit_message').disabled = false;
        setup();
        sendMessage(JSON.stringify(msg))
    }

    // trigger to getting a message from the socket
    function socketMessage (e) {
        console.log(`Message from socket: ${e.data}`);

        let parsedMsg = parseMessage(e.data);
        if (parsedMsg.type == "join") {
            appendMessage(e.data, 0);
            let messages = parseMessage(parsedMsg.messages);
            for(let i = 0; i < Object.keys(messages).length; i++) {
                if (messages[i].length == 0) continue;
                appendMessage(JSON.stringify(messages[i]));
            }
        }
        else if (parsedMsg.type == "delete") {
            let element = document.getElementById(parsedMsg.mid);
            element.parentNode.removeChild(element);
        }
        else {
            appendMessage(e.data, 0);
        }
    }

    // closing the socket connection
    var socketClose = (e) => {
        var msg;
        console.log(e);
        if(e.wasClean) {
            console.log("The connection closed cleanly");
            msg = {
                type: 'left',
                sender: 'Browser',
                text: 'The connection closed cleanly'
            }
        }
        else {
            console.log("The connection closed for some reason");
            var msg = {
                type: 'left',
                sender: 'Browser',
                text: 'The connection closed for some reason'
            }
        }
        document.getElementById('message').disabled = true;
        document.getElementById('submit_message').disabled = true;
        appendMessage(JSON.stringify(msg));
    }

    // error tracking
    var socketError = (e) => {
        console.log("WebSocket Error");
        console.log(e);
    }

    // socket event triggers (listeners)
    socket.addEventListener("open", socketOpen);
    socket.addEventListener("message", socketMessage);
    socket.addEventListener("close", socketClose);
    socket.addEventListener("error", socketError);

    // mod functions()
    <?php
        if (intval($_user['user_type']) > 0) {
        echo '
        function showModerationPanel(e) {
            console.log(e)
            var dropdowns = document.getElementsByClassName("dropdown");
            for (let i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains("show")) {
                    openDropdown.classList.remove("show");
                }
            }
            document.getElementById("dropdown-"+e.id).classList.toggle("show");
        }

        window.onclick = function(event) {
            if (!event.target.matches(".dropbtn")) {
                var dropdowns = document.getElementsByClassName("dropdown");
                for (let i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains("show")) {
                        openDropdown.classList.remove("show");
                    }
                }
            }
        }

        $(".moderation_ban").on("click", function() {
            alert("test");
        });
        
        async function ban(id) {
            await $(document).ready();
            let login = await document.getElementById(id).getAttribute("author");
            Swal.fire({
                title: "Блокировка пользователя",
                html: "Вы уверены, что хотите заблокировать пользователя <b>" + login + "</b>?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
                confirmButtonText: "Заблокировать",
                cancelButtonText: "Отменить"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "post",
                        url: "/api/moderation/ban",
                        data: "login="+login,
                        dataType: "json",
                        success: function(data){
                            if (data.result == 1) {
                                Swal.fire(
                                    "Успешно!",
                                    data.text,
                                    "success"
                                ).then((result) => {
    
                                });
                            }
                            else {
                                Swal.fire(
                                    "Произошла ошибка",
                                    data.text,
                                    "error"
                                ).then((result) => {
    
                                });
                            }
                        }
                    });
                }
            })
        }
        
        function delete_message(id) {
            let msg = {
                type: "delete",
                mid: id
            };
            msg = JSON.stringify(msg);
            console.log(msg)
            sendMessage(msg);
        }
    ';
    }
    ?>

</script>
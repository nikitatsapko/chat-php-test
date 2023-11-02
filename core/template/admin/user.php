<div class="container">
    <style>
        table {
            max-width: 50%;
            border: 1px solid #ccc;
            border-collapse: collapse;
        }
        thead {
            border: 1px solid #ccc;
        }
        th, td {
            border-left: 1px solid #ccc;
            white-space: nowrap;
            padding: 5px;
        }
        td:nth-last-child(-n+2) {
            text-align: right;
        }
    </style>
    <p class="page-title">Редактирование пользователя <?php echo $user['login'] ?></p>
    <div class="form">
        <div class="row" style="width: 100%;">
            <div style="float:left; width: 50%">
                <div class="col-12">
                    <div class="in">
                        <label for="login">Имя пользователя:</label><br>
                        <input id="login" type="text" disabled placeholder="Имя протщрваиеоя" value="<?php echo $user['login'] ?>">
                    </div>
                </div>
                <div class="col-12">
                    <div class="in">
                        <label for="password">Пароль:</label><br>
                        <input id="password" type="password" placeholder="Новый пароль">
                    </div>
                </div>
                <div class="col-12">
                    <div class="in">
                        <label for="role">Роль:</label><br>
                        <select id="role">
                            <option value="<?php echo $user['user_type']; ?>"><?php echo getNameRole($user['user_type']); ?></option>
                            <option>===========</option>
                            <?php for ($type = 0; $type < 3; $type++): ?>
                                <?php if($type == $user['user_type']) continue; ?>
                                <option value="<?php echo $type; ?>"><?php echo getNameRole($type); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="in">
                        <label for="banned">Забанен?</label><br>
                        <input id="banned" type="checkbox" placeholder="Новый пароль" <?php echo ($user['blocked'] ? "checked" : "") ?>>
                    </div>
                </div>
                <div class="col-12"><br><br>
                    <button id="submit" type="submit" class="submit" onclick="edit();">Сохранить</button>
                </div>
            </div>
        </div>
        <p class="result"></p>
    </div>
</div>
<script>
    function edit() {
        let banned = 1;
        if (document.getElementById('banned').checked)
            banned = 2;
        $.ajax({
            type: 'post',
            url: "/api/user/edit",
            data: 'id=<?php echo $user['id']?>&login='+$("#login").val()+'&password='+$("#password").val()+'&role='+$("#role").val()+'&banned='+banned,
            dataType: 'json',
            success: function(data){
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: true,
                    timer: 5500,
                    timerProgressBar: true
                });
                if (data.result == 1) {
                    Toast.fire({
                        icon: 'success',
                        title: data.text,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            return reload();
                        }
                    });

                    document.getElementById('submit').onclick = "";
                    document.getElementById('password').disabled = true;
                    document.getElementById('role').disabled = true;

                    function reload() {
                        return location.replace('/admin/users');
                    }

                    setTimeout(reload, 5575);
                }
                else {
                    Toast.fire({
                        icon: 'error',
                        title: data.text
                    });
                }
            }
        });
    }
</script>

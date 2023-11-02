<div class="container">
    <p class="page-title">Изменение вашего пароля</p>
    <div class="form">
        <div class="col-12">
            <div class="in">
                <br>
                <label for="password">Новый пароль:</label><br>
                <input id="password" type="password" placeholder="Новый пароль">
            </div>
        </div>
        <div class="col-12">
            <div class="in">
                <label for="confirm-password">Подтверждение нового пароля:</label><br>
                <input id="confirm-password" type="password" placeholder="Подтверждение нового пароя">
            </div>
        </div>
        <div class="col-12">
            <div class="in">
                <br><br>
                <button type="submit" class="submit" onclick="save();">Обновить</button>
            </div>
        </div>
    </div>
</div>
<script>
    function save() {
        if (document.getElementById("password").value != document.getElementById("confirm-password").value) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: true,
                timer: 4500,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'error',
                title: 'Пароли не совпадают, попробуйте снова'
            });
            return;
        }
        $.ajax({
            type: 'post',
            url: "/api/user/changepassword",
            data: 'password='+$("#password").val(),
            dataType: 'json',
            success: function(data){
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: true,
                    timer: 7500,
                    timerProgressBar: true
                });
                if (data.result == 1) {
                    Toast.fire({
                        icon: 'info',
                        title: data.text,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            return location.replace("/");
                        }
                    });

                    function reload() {
                        return location.replace("/");
                    }

                    setTimeout(reload, 7575);
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
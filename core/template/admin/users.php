<style type="text/css">
    .table thead th {
        background-color: #428BCA;
        border-color: #428BCA !important;
        color: #FFF;
    }
    .table-bordered {
        box-shadow: 0px 35px 50px rgb(0 0 0 / 20%);
    }
</style>
<div class="container">
    <div class="form">
        <p class="page-title">Список пользователей</p>
        <div class="row">
            <div class="col-12 in" id="server-online">
                <table class="table table-bordered table-striped">
                    <thead><tr>
                        <th>Пользователь</th>
                        <th>Роль</th>
                        <th>Действия</th>
                    </tr></thead>
                    <tbody>
                        <?php
                            for($i = 0; $i < $query->num_rows; $i++) {
                                $result = $query->fetch_assoc();
                                echo '<tr>';
                                echo '<th>' . $result['login'] . '</th>';
                                echo '<th>' . getNameRole($result['user_type']) . '</th>';
                                echo '<th><a href="/admin/users/' . $result['id'] . '"><img src="https://www.svgrepo.com/show/522527/edit-3.svg" height="20" width="20"></a></th>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
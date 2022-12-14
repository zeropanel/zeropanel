{include file='admin/main.tpl'}

<main class="content">
    <div class="content-header ui-content-header">
        <div class="container">
            <h1 class="content-heading">编辑商品</h1>
        </div>
    </div>
    <div class="container">
        <div class="col-lg-12 col-sm-12">
            <section class="content-inner margin-top-no">

                <div class="card">
                    <div class="card-main">
                        <div class="card-inner">
                            <div class="form-group form-group-label">
                                <label class="floating-label" for="name">名称</label>
                                <input class="form-control maxwidth-edit" id="name" type="text" value="{$shop->name}">
                            </div>


                            <div class="form-group form-group-label">
                                <label class="floating-label" for="price">价格</label>
                                <input class="form-control maxwidth-edit" id="price" type="text" value="{$shop->price}">
                            </div>
                            
                            <div class="form-group form-group-label">
                                <label class="floating-label" for="price">产品类型</label>
                                <select class="form-control maxwidth-edit" id="type">
                                    <option value="cycle" {if $shop->type == "cycle"}selected{/if}>周期产品</option>
                                    <option value="traffic" {if $shop->type == "traffic"}selected{/if}>流量产品</option>
                                    <option value="other" {if $shop->type == "other"}selected{/if}>其他产品</option>
                                </select>
                            </div>
                            <div class="form-group form-group-label">
                                <label class="floating-label" for="price">排序</label>
                                <input class="form-control maxwidth-edit" id="sort" value="{$shop->sort}" type="text">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-main">
                        <div class="card-inner">

                            <div class="form-group form-group-label">
                                <label class="floating-label" for="traffic">流量（GB）</label>
                                <input class="form-control maxwidth-edit" id="traffic" type="text"
                                       value="{$shop->bandwidth()}">
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-main">
                        <div class="card-inner">

                            <div class="form-group form-group-label">
                                <label class="floating-label" for="stock">限制购买总数量 (该套餐当前已销售 {$shop->sales} 份)</label>
                                <input class="form-control maxwidth-edit" id="stock" type="text" value="{$shop->stock}">
                                <p class="form-control-guide"><i class="material-icons">info</i>按所有用户中, 生效套餐为该套餐的累计, 超过该数量其他用户则不能购买, 0 为不限制</p>
                                <p class="form-control-guide"><i class="material-icons">info</i>如果其他用户套餐到期后没有再购买该套餐, 则自动有1个名额可以购买</p>
                            </div>

                        </div>
                    </div>
                </div>

                
                <div class="card">
                    <div class="card-main">
                        <div class="card-inner">
                        
                            <div class="form-group form-group-label">
                                <label class="floating-label" for="node_group">用户群组</label>
                                <input class="form-control maxwidth-edit" id="node_group" type="text" value="{$shop->node_group()}">
                                <p class="form-control-guide"> <i class="material-icons">info</i> 购买该套餐将用户修改成此分组下,   -1 不分配, 保持用户默认</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-main">
                        <div class="card-inner">

                            <div class="form-group form-group-label">
                                <label class="floating-label" for="class">等级</label>
                                <input class="form-control maxwidth-edit" id="class" type="text"
                                       value="{$shop->user_class()}">
                            </div>

                            <div class="form-group form-group-label">
                                <label class="floating-label" for="class_expire">等级有效期天数</label>
                                <input class="form-control maxwidth-edit" id="class_expire" type="text"
                                       value="{$shop->class_expire()}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-main">
                        <div class="card-inner">
                            <div class="form-group form-group-label">
                                <label class="floating-label" for="reset_exp">多少天内</label>
                                <input class="form-control maxwidth-edit" id="reset_exp" type="number"
                                       value="{$shop->reset_exp()}">
                            </div>


                            <div class="form-group form-group-label">
                                <label class="floating-label" for="reset">每多少天</label>
                                <input class="form-control maxwidth-edit" id="reset" type="number"
                                       value="{$shop->reset()}">
                            </div>

                            <div class="form-group form-group-label">
                                <label class="floating-label" for="reset_value">重置流量为多少G</label>
                                <input class="form-control maxwidth-edit" id="reset_value" type="number"
                                       value="{$shop->reset_value()}">
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card">
                    <div class="card-main">
                        <div class="card-inner">
                            <div class="form-group form-group-label">
                                <label class="floating-label" for="speed_limit">端口限速</label>
                                <input class="form-control maxwidth-edit" id="speed_limit" type="number"
                                       value="{$shop->speedlimit()}">
                            </div>


                            <div class="form-group form-group-label">
                                <label class="floating-label" for="ip_limit">IP限制</label>
                                <input class="form-control maxwidth-edit" id="ip_limit" type="number"
                                       value="{$shop->connector()}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-main">
                        <div class="card-inner">

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-10 col-md-push-1">
                                        <button id="submit" type="submit"
                                                class="btn btn-block btn-brand waves-attach waves-light">保存
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {include file='dialog.tpl'}


        </div>


    </div>
</main>

{include file='admin/footer.tpl'}


<script>
    window.addEventListener('load', () => {
        function submit() {           
            let data = {
                name: $$getValue('name'),
                price: $$getValue('price'),
                type: $$getValue('type'),
                sort: $$getValue('sort'),
                traffic: $$getValue('traffic'),
                speed_limit: $$getValue('speed_limit'),
                ip_limit: $$getValue('ip_limit'),
                class: $$getValue('class'),
                class_expire: $$getValue('class_expire'),
                reset: $$getValue('reset'),
                reset_value: $$getValue('reset_value'),
                reset_exp: $$getValue('reset_exp'),
                node_group: $$getValue('node_group'),
                stock: $$getValue('stock'),
            }
            $.ajax({
                type: "PUT",
                url: "/admin/shop/{$shop->id}",
                dataType: "json",
                data,
                success: data => {
                    if (data.ret) {
                        $("#result").modal();
                        $$.getElementById('msg').innerHTML = data.msg;
                        window.setTimeout("location.href='/admin/shop'", {$config['jump_delay']});
                    } else {
                        $("#result").modal();
                        $$.getElementById('msg').innerHTML = data.msg;
                    }
                },
                error: jqXHR => {
                    $("#result").modal();
                    $$.getElementById('msg').innerHTML = `发生错误：${
                            jqXHR.status
                            }`;
                }
            });
        }
        $("html").keydown(event => {
            if (event.keyCode === 13) {
                login();
            }
        });
        $$.getElementById('submit').addEventListener('click', submit);
    })
</script>
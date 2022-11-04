window.addEventListener('load', function() {
    let currentProvider = null;
    function doSign(accounts){
        currentProvider = web3.currentProvider;
        web3 = new Web3(currentProvider);
        // web3.setProvider(currentProvider);
        //如果用户同意了登录请求，你就可以拿到用户的账号
        web3.eth.defaultAccount = accounts[0];
        let rightnow = (Date.now() / 1000).toFixed(0)
        let sortanow = rightnow - (rightnow % 600)
        console.log('Signning in to firedao' + 'at' + sortanow, web3.eth.defaultAccount);
        web3.eth.personal.sign('Signning in to firedao' + 'at' + sortanow, web3.eth.defaultAccount, "test password!").then(
            function(data){
                $.post("./index.php?action=sign",{sign:data,address:web3.eth.defaultAccount},function(result){
                    console.log(result)
                });
            }
        )
    }
    if (smf_member_id == 0) {
        ethereum.enable()
            .catch(function(reason) {
                //如果用户拒绝了登录请求
                if (reason === "User rejected provider access") {
                    // 用户拒绝登录后执行语句；
                } else {
                    // 本不该执行到这里，但是真到这里了，说明发生了意外
                    Message.warning("There was a problem signing you in");
                }
            }).then(function(accounts) {
            // 判断是否连接以太
            // if (ethereum.networkVersion !== desiredNetwork) {}
            doSign(accounts)
        });
    }
    ethereum.on('accountsChanged', function (accounts) {
        doSign(accounts)
    })

})

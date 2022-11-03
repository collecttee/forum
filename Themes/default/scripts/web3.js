window.addEventListener('load', function() {
    // Checking if Web3 has been injected by the browser (Mist/MetaMask)
    // if (typeof web3 !== 'undefined') {
    //     // Use Mist/MetaMask's provider
    //     web3 = new Web3("https://testnet.bscscan.com/");
    //     console.log(web3);
    //     web3.eth.sign("Signning in to", web3.eth.accounts[0]);
    // } else {
    //     alert('please install MetaMask');
    // }
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
        let currentProvider = web3.currentProvider;
        web3 = new Web3(currentProvider);
        // web3.setProvider(currentProvider);
        //如果用户同意了登录请求，你就可以拿到用户的账号
        web3.eth.defaultAccount = accounts[0];
        let rightnow = (Date.now() / 1000).toFixed(0)
        let sortanow = rightnow - (rightnow % 600)
        console.log('Signning in to ' + document.domain + 'at' + sortanow, web3.eth.defaultAccount);
       web3.eth.personal.sign('Signning in to ' + document.domain + 'at' + sortanow, web3.eth.defaultAccount, "test password!").then(
           function(data){
               $.post("./index.php?action=sign",{sign:data,address:web3.eth.defaultAccount},function(result){
                   console.log(result)
               });
           }
       )


    });
})

import * as ecc from "tiny-secp256k1";
import * as bitcoin from "bitcoinjs-lib";
import { verifyMessage } from "@unisat/wallet-utils";

bitcoin.initEccLib(ecc);

const args = process.argv; // 获取命令行参数列表

const message = "Sign in to this forum, your data is secure!";
const signature = args[2];
const publicKeyHex = args[3];
const result = verifyMessage(publicKeyHex,message,signature);
if (result){
    // 将十六进制的公钥转换为 Buffer 对象
    let publicKeyBuffer = Buffer.from(publicKeyHex, 'hex');
    // console.log(publicKeyBuffer)
    publicKeyBuffer = publicKeyBuffer.slice(1, 33);
    // console.log(publicKeyBuffer)
// 使用测试网的网络参数
    const network = bitcoin.networks.testnet;
// 创建一个 Taproot 测试网地址
    const { address } = bitcoin.payments.p2tr({ internalPubkey: publicKeyBuffer, network });
    console.log(address);
}else{
    console.log("error")
}



import * as ecc from "tiny-secp256k1";
import * as bitcoin from "bitcoinjs-lib";
bitcoin.initEccLib(ecc);
const args = process.argv;
// console.log(args)
const script = args[2];
const address = bitcoin.address.fromOutputScript(Buffer.from(script, 'hex'), bitcoin.networks.testnet)
console.log(address)

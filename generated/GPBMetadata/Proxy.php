<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: proxy.proto

namespace GPBMetadata;

class Proxy
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(hex2bin(
            "0ab91f0a0b70726f78792e70726f746f121c63656e747269667567616c2e63656e7472696675676f2e70726f7879223d0a0a446973636f6e6e656374120c0a04636f646518012001280d120e0a06726561736f6e18022001280912110a097265636f6e6e65637418032001280822390a054572726f72120c0a04636f646518012001280d120f0a076d65737361676518022001280912110a0974656d706f7261727918032001280822a7010a0e436f6e6e65637452657175657374120e0a06636c69656e7418012001280912110a097472616e73706f727418022001280912100a0870726f746f636f6c18032001280912100a08656e636f64696e67180420012809120c0a0464617461180a2001280c120f0a0762363464617461180b20012809120c0a046e616d65180c20012809120f0a0776657273696f6e180d2001280912100a086368616e6e656c73180e2003280922ac010a105375627363726962654f7074696f6e7312110a096578706972655f6174180120012803120c0a04696e666f18022001280c120f0a07623634696e666f180320012809120c0a046461746118042001280c120f0a076236346461746118052001280912470a086f7665727269646518062001280b32352e63656e747269667567616c2e63656e7472696675676f2e70726f78792e5375627363726962654f7074696f6e4f7665727269646522f0020a0d436f6e6e656374526573756c74120c0a047573657218012001280912110a096578706972655f6174180220012803120c0a04696e666f18032001280c120f0a07623634696e666f180420012809120c0a046461746118052001280c120f0a076236346461746118062001280912100a086368616e6e656c7318072003280912430a047375627318082003280b32352e63656e747269667567616c2e63656e7472696675676f2e70726f78792e436f6e6e656374526573756c742e53756273456e747279120c0a046d65746118092001280c123e0a0463617073180a2003280b32302e63656e747269667567616c2e63656e7472696675676f2e70726f78792e4368616e6e656c734361706162696c6974791a5b0a0953756273456e747279120b0a036b6579180120012809123d0a0576616c756518022001280b322e2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e5375627363726962654f7074696f6e733a02380122440a124368616e6e656c734361706162696c69747912100a086368616e6e656c73180120032809120d0a05616c6c6f77180220032809120d0a056d6174636818032001280922c0010a0f436f6e6e656374526573706f6e7365123b0a06726573756c7418012001280b322b2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e436f6e6e656374526573756c7412320a056572726f7218022001280b32232e63656e747269667567616c2e63656e7472696675676f2e70726f78792e4572726f72123c0a0a646973636f6e6e65637418032001280b32282e63656e747269667567616c2e63656e7472696675676f2e70726f78792e446973636f6e6e65637422730a0e5265667265736852657175657374120e0a06636c69656e7418012001280912110a097472616e73706f727418022001280912100a0870726f746f636f6c18032001280912100a08656e636f64696e67180420012809120c0a0475736572180a20012809120c0a046d657461180b2001280c22a0010a0d52656672657368526573756c74120f0a076578706972656418012001280812110a096578706972655f6174180220012803120c0a04696e666f18032001280c120f0a07623634696e666f180420012809120c0a046d65746118052001280c123e0a046361707318062003280b32302e63656e747269667567616c2e63656e7472696675676f2e70726f78792e4368616e6e656c734361706162696c69747922c0010a0f52656672657368526573706f6e7365123b0a06726573756c7418012001280b322b2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e52656672657368526573756c7412320a056572726f7218022001280b32232e63656e747269667567616c2e63656e7472696675676f2e70726f78792e4572726f72123c0a0a646973636f6e6e65637418032001280b32282e63656e747269667567616c2e63656e7472696675676f2e70726f78792e446973636f6e6e65637422b4010a1053756273637269626552657175657374120e0a06636c69656e7418012001280912110a097472616e73706f727418022001280912100a0870726f746f636f6c18032001280912100a08656e636f64696e67180420012809120c0a0475736572180a20012809120f0a076368616e6e656c180b20012809120d0a05746f6b656e180c20012809120c0a046d657461180d2001280c120c0a0464617461180e2001280c120f0a0762363464617461180f20012809221a0a09426f6f6c56616c7565120d0a0576616c7565180120012808221b0a0a496e74333256616c7565120d0a0576616c756518012001280522de020a175375627363726962654f7074696f6e4f7665727269646512390a0870726573656e636518012001280b32272e63656e747269667567616c2e63656e7472696675676f2e70726f78792e426f6f6c56616c7565123b0a0a6a6f696e5f6c6561766518022001280b32272e63656e747269667567616c2e63656e7472696675676f2e70726f78792e426f6f6c56616c7565123f0a0e666f7263655f7265636f7665727918032001280b32272e63656e747269667567616c2e63656e7472696675676f2e70726f78792e426f6f6c56616c756512420a11666f7263655f706f736974696f6e696e6718042001280b32272e63656e747269667567616c2e63656e7472696675676f2e70726f78792e426f6f6c56616c756512460a15666f7263655f707573685f6a6f696e5f6c6561766518052001280b32272e63656e747269667567616c2e63656e7472696675676f2e70726f78792e426f6f6c56616c756522ba010a0f537562736372696265526573756c7412110a096578706972655f6174180120012803120c0a04696e666f18022001280c120f0a07623634696e666f180320012809120c0a046461746118042001280c120f0a076236346461746118052001280912470a086f7665727269646518062001280b32352e63656e747269667567616c2e63656e7472696675676f2e70726f78792e5375627363726962654f7074696f6e4f76657272696465120d0a05616c6c6f7718072003280922c4010a11537562736372696265526573706f6e7365123d0a06726573756c7418012001280b322d2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e537562736372696265526573756c7412320a056572726f7218022001280b32232e63656e747269667567616c2e63656e7472696675676f2e70726f78792e4572726f72123c0a0a646973636f6e6e65637418032001280b32282e63656e747269667567616c2e63656e7472696675676f2e70726f78792e446973636f6e6e65637422a3010a0e5075626c69736852657175657374120e0a06636c69656e7418012001280912110a097472616e73706f727418022001280912100a0870726f746f636f6c18032001280912100a08656e636f64696e67180420012809120c0a0475736572180a20012809120f0a076368616e6e656c180b20012809120c0a0464617461180c2001280c120f0a0762363464617461180d20012809120c0a046d657461180e2001280c22440a0d5075626c697368526573756c74120c0a046461746118012001280c120f0a076236346461746118022001280912140a0c736b69705f686973746f727918032001280822c0010a0f5075626c697368526573706f6e7365123b0a06726573756c7418012001280b322b2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e5075626c697368526573756c7412320a056572726f7218022001280b32232e63656e747269667567616c2e63656e7472696675676f2e70726f78792e4572726f72123c0a0a646973636f6e6e65637418032001280b32282e63656e747269667567616c2e63656e7472696675676f2e70726f78792e446973636f6e6e656374229e010a0a52504352657175657374120e0a06636c69656e7418012001280912110a097472616e73706f727418022001280912100a0870726f746f636f6c18032001280912100a08656e636f64696e67180420012809120c0a0475736572180a20012809120e0a066d6574686f64180b20012809120c0a0464617461180c2001280c120f0a0762363464617461180d20012809120c0a046d657461180e2001280c222a0a09525043526573756c74120c0a046461746118012001280c120f0a076236346461746118022001280922b8010a0b525043526573706f6e736512370a06726573756c7418012001280b32272e63656e747269667567616c2e63656e7472696675676f2e70726f78792e525043526573756c7412320a056572726f7218022001280b32232e63656e747269667567616c2e63656e7472696675676f2e70726f78792e4572726f72123c0a0a646973636f6e6e65637418032001280b32282e63656e747269667567616c2e63656e7472696675676f2e70726f78792e446973636f6e6e656374329a040a1643656e7472696675676f50726f78795365727669636512660a07436f6e6e656374122c2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e436f6e6e656374526571756573741a2d2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e436f6e6e656374526573706f6e736512660a0752656672657368122c2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e52656672657368526571756573741a2d2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e52656672657368526573706f6e7365126c0a09537562736372696265122e2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e537562736372696265526571756573741a2f2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e537562736372696265526573706f6e736512660a075075626c697368122c2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e5075626c697368526571756573741a2d2e63656e747269667567616c2e63656e7472696675676f2e70726f78792e5075626c697368526573706f6e7365125a0a0352504312282e63656e747269667567616c2e63656e7472696675676f2e70726f78792e525043526571756573741a292e63656e747269667567616c2e63656e7472696675676f2e70726f78792e525043526573706f6e736542315a1363656e7472696675676f2f70726f78792f7631ca0219526f616452756e6e65725c43656e7472696675676f5c44544f620670726f746f33"
        ), true);

        static::$is_initialized = true;
    }
}

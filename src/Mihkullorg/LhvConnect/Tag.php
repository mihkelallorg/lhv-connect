<?php

namespace Mihkullorg\LhvConnect;

class Tag {

    const
        ACCOUNT = "Acct",
        ACCOUNT_IDENTIFICATION = "Id",
        ACCOUNT_OWNER = "AcctOwnr",
        AMOUNT = "Amt",
        CONTROL_SUM = "CtrlSum",
        CURRENCY = "Ccy",
        BATCH_BOOKING = "BtchBookg",
        BENEFICIARY = "Cdtr",
        BENEFICIARY_ACCOUNT = "CdtrAcct",
        BENEFICIARY_ACCOUNT_IDENTIFICATION = "Id",
        BENEFICIARY_AGENT = "CdtrAgt",
        BENEFICIARY_NAME = "Nm",
        BIC = "BIC",
        CREATION_DATETIME = "CreDtTm",
        CREDIT_TRANSFER_TRANSACTION_INFORMATION = "CdtTrfTxInf",
        CHARGES_BEARER = "ChrgBr",
        DOCUMENT = "Document",
        END_TO_END_IDENTIFICATION = "EndToEndId",
        FINANCIAL_INSTITUTION_IDENTIFICATION = "FinInstnId",
        FROM_DATE = "FrDt",
        FROM_TO_DATE = "FrToDt",
        FROM_TO_TIME = "FrToTm",
        GROUP_HEADER = "GrpHdr",
        IBAN = "IBAN",
        IDENTIFICATION = "Id",
        INITIATING_PARTY = "InitgPty",
        INNER_NUMBER_OF_TRANSACTIONS = "NbOfTxs",
        INNER_CONTROL_SUM = "CtrlSum",
        INSTRUCTED_AMOUNT = "InstdAmt",
        LOCAL_INSTRUMENT = "LclInstrm",
        MERCHANT_PAYMENT_TYPE = "Type",
        MESSAGE_IDENTIFICATION = "MsgId",
        NAME = "Nm",
        NUMBER_OF_TRANSACTIONS = "NbOfTxs",
        PARTY = "Pty",
        PAYMENT_IDENTIFICATION = "PmtId",
        PAYMENT_INFORMATION = "PmtInf",
        PAYMENT_INFORMATION_IDENTIFICATION = "PmtInfId",
        PAYMENT_METHOD = "PmtMtd",
        PAYMENT_TYPE_INFORMATION = "PmtTpInf",
        PERIOD_START = "PeriodStart",
        PERIOD_END = "PeriodEnd",
        PROPRIETARY = "Prtry",
        REMITTANCE_INFORMATION = "RmtInf",
        REMITTER = "Dbtr",
        REMITTER_ACCOUNT = "DbtrAcct",
        REMITTER_ACCOUNT_IDENTIFICATION = "Id",
        REMITTER_AGENT = "DbtrAgt",
        REMITTER_NAME = "Nm",
        REPORTING_PERIOD = "RptgPrd",
        REPORTING_REQUEST = "RptgReq",
        REQUESTED_EXECUTION_DATE = "ReqdExctnDt",
        REQUESTED_MESSAGE_NAME_IDENTIFICATION = "ReqdMsgNmId",
        TO_DATE = "ToDt",
        TO_TIME = "ToTm",
        TYPE = "Tp",
        UNSTRUCTURED = "Ustrd",

        /**
         * Request name tags
         */

        ACCOUNT_STATEMENT_REQUEST = "AcctRptgReq",
        MERCHANT_REPORT_REQUEST = "MerchantReportRequest",
        PAYMENT_INITIATION_REQUEST = "CstmrCdtTrfInitn",

        ACCOUNT_STATEMENT_RESPONSE = "BkToCstmrStmt",
        MERCHANT_REPORT_RESPONSE = "BkToCstmrDbtCdtNtfctn"
    ;

}
<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Enum;

enum DnsRecordType: string
{
    case A      = 'A';
    case AAAA   = 'AAAA';
    case ALIAS  = 'ALIAS';
    case CAA    = 'CAA';
    case CERT   = 'CERT';
    case CNAME  = 'CNAME';
    case DNSKEY = 'DNSKEY';
    case DS     = 'DS';
    case HINFO  = 'HINFO';
    case HTTPS  = 'HTTPS';
    case LOC    = 'LOC';
    case MX     = 'MX';
    case NAPTR  = 'NAPTR';
    case NS     = 'NS';
    case PTR    = 'PTR';
    case RP     = 'RP';
    case SMIMEA = 'SMIMEA';
    case SPF    = 'SPF';
    case SRV    = 'SRV';
    case SSHFP  = 'SSHFP';
    case SVCB   = 'SVCB';
    case TLSA   = 'TLSA';
    case TXT    = 'TXT';
    /** Web redirect — ClouDNS-specific */
    case WR     = 'WR';
}

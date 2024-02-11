#!/usr/bin/env bash

# exit when any command fails
set -e

# https://devopscube.com/create-self-signed-certificates-openssl/

# Create root CA & Private key
if [[ ! -f /ssl/internal.ca.key ]]; then
  openssl req \
    -x509 \
    -sha256 -days 7300 \
    -nodes \
    -newkey rsa:2048 \
    -subj "/CN=internal" \
    -keyout /ssl/internal.ca.key -out /ssl/internal.ca.cert
fi

# Generate Private key
if [[ ! -f /ssl/internal.key ]]; then
  openssl genrsa -out /ssl/internal.key 2048
fi

# Create csf conf
cat > /ssl/internal.csf.conf <<EOF
[ req ]
default_bits = 2048
prompt = no
default_md = sha256
req_extensions = req_ext
distinguished_name = dn

[ dn ]
CN = internal

[ req_ext ]
subjectAltName = @alt_names

[ alt_names ]
DNS.1 = *.wp.internal
DNS.2 = wp.internal
DNS.3 = internal
DNS.4 = *.wp.localhost
DNS.5 = wp.localhost
DNS.6 = localhost
IP.1 = 127.0.0.1
IP.2 = 127.0.0.2

EOF

# create CSR request using private key
openssl req -new -key /ssl/internal.key -out /ssl/internal.csr.key -config /ssl/internal.csf.conf

# Create a external config file for the certificate
cat > /ssl/internal.cert.conf <<EOF

authorityKeyIdentifier=keyid,issuer
basicConstraints=CA:FALSE
keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
subjectAltName = @alt_names

[alt_names]
DNS.1 = *.wp.internal
DNS.2 = wp.internal
DNS.3 = internal
DNS.4 = *.wp.localhost
DNS.5 = wp.localhost
DNS.6 = localhost
IP.1 = 127.0.0.1
IP.2 = 127.0.0.2

EOF

# Create SSl with self signed CA
openssl x509 -req \
    -in /ssl/internal.csr.key \
    -CA /ssl/internal.ca.cert -CAkey /ssl/internal.ca.key \
    -CAcreateserial -out /ssl/internal.cert \
    -days 825 \
    -sha256 -extfile /ssl/internal.cert.conf

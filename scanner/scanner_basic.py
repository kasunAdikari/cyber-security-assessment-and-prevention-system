import nmap, sys

def basic_scan(target):
    nm = nmap.PortScanner()
    nm.scan(hosts=target, arguments="-T4")
    results = []
    for host in nm.all_hosts():
        results.append(f"Host: {host}")
        results.append(f"State: {nm[host].state()}")
        for proto in nm[host].all_protocols():
            for port in sorted(nm[host][proto].keys()):
                service = nm[host][proto][port]
                results.append(f"Port: {port}/{proto}  State: {service['state']}")
    return "\n".join(results)

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python scanner_basic.py <target>")
        sys.exit(1)
    print(basic_scan(sys.argv[1]))

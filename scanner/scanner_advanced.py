import nmap, sys

def advanced_scan(target):
    nm = nmap.PortScanner()
    nm.scan(hosts=target, arguments="-sC -sV -O -T4")
    results = []
    for host in nm.all_hosts():
        results.append(f"Host: {host}")
        results.append(f"State: {nm[host].state()}")
        if 'osmatch' in nm[host]:
            for os in nm[host]['osmatch']:
                results.append(f"Possible OS: {os['name']} (Accuracy: {os['accuracy']}%)")
        for proto in nm[host].all_protocols():
            for port in sorted(nm[host][proto].keys()):
                service = nm[host][proto][port]
                results.append(f"Port: {port}/{proto}  State: {service['state']}")
                results.append(f"  Service: {service['name']}  Version: {service.get('version', '')}")
                if 'script' in service:
                    for script, output in service['script'].items():
                        results.append(f"  [SCRIPT] {script}: {output}")
    return "\n".join(results)

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python scanner_advanced.py <target>")
        sys.exit(1)
    print(advanced_scan(sys.argv[1]))

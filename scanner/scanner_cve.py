import sys
import subprocess
import re

def cve_scan(target):
    try:
        # Run nmap with vulners script
        cmd = ["nmap", "-sV", "--script", "vulners", target]
        result = subprocess.check_output(cmd, stderr=subprocess.STDOUT, text=True)

        # Extract CVE numbers using regex
        cves = re.findall(r"CVE-\d{4}-\d{4,7}", result)

        if cves:
            unique_cves = sorted(set(cves))
            return "\n".join(unique_cves)
        else:
            return "No CVEs found."
    except subprocess.CalledProcessError as e:
        return f"Error running scan:\n{e.output}"

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python scanner_cve.py <target>")
        sys.exit(1)
    
    target = sys.argv[1]
    print(cve_scan(target))

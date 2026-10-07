"""
BITC AI Trust Platform — Algorithmic Bias & Fairness Audit Engine (Sample)
Author: SOADAN Koffi Sylvain
Description: Computes Disparate Impact Ratio and Demographic Parity Difference
in accordance with African Union Continental AI Strategy ethical guidelines.
"""

from typing import Dict, Union


def calculate_disparate_impact(privileged_positive: int, privileged_total: int,
                               unprivileged_positive: int, unprivileged_total: int) -> Dict[str, Union[float, int, str]]:
    """
    Computes Disparate Impact Ratio:
    (Positive Rate for Unprivileged Group) / (Positive Rate for Privileged Group)
    The 4/5ths (80%) rule: A ratio below 0.80 typically suggests potential adverse impact.
    """
    if privileged_total <= 0 or unprivileged_total <= 0:
        raise ValueError("Group totals must be greater than zero.")
    if privileged_positive < 0 or unprivileged_positive < 0:
        raise ValueError("Positive counts cannot be negative.")
    if privileged_positive > privileged_total or unprivileged_positive > unprivileged_total:
        raise ValueError("Positive counts cannot exceed their group totals.")

    rate_priv = privileged_positive / privileged_total
    rate_unpriv = unprivileged_positive / unprivileged_total

    di_ratio = rate_unpriv / rate_priv if rate_priv > 0 else 0.0
    dp_diff = rate_priv - rate_unpriv

    # Compute a normalized Trust Score (0-100)
    # 1.0 ratio = 100 points, falling below 0.80 penalizes score exponentially
    if di_ratio >= 0.80 and di_ratio <= 1.25:
        trust_score = int(90 + (1.0 - abs(1.0 - di_ratio)) * 10)
    elif di_ratio >= 0.60:
        trust_score = int(70 + (di_ratio - 0.60) * 100)
    else:
        trust_score = max(10, int(di_ratio * 100))

    compliance = "PASSED (AU AI Strategy Compliant)" if di_ratio >= 0.80 else "FAILED (Adverse Impact Detected)"

    return {
        "privileged_positive_rate": round(rate_priv, 4),
        "unprivileged_positive_rate": round(rate_unpriv, 4),
        "disparate_impact_ratio": round(di_ratio, 4),
        "demographic_parity_difference": round(dp_diff, 4),
        "trust_score": trust_score,
        "compliance_status": compliance
    }

if __name__ == "__main__":
    print("================================================================")
    print(" BITC AUDIT ENGINE — ALGORITHMIC FAIRNESS SAMPLE RUN")
    print(" Context: Evaluation of Automated Public Credit Assessment")
    print("================================================================\n")

    # Example: Evaluating acceptance rate between Urban vs Rural applicants
    audit_results = calculate_disparate_impact(
        privileged_positive=750, privileged_total=1000,   # Urban (75% approval)
        unprivileged_positive=620, unprivileged_total=1000 # Rural (62% approval)
    )

    for k, v in audit_results.items():
        print(f" {k.replace('_', ' ').capitalize():<32} : {v}")

    print("\n================================================================")

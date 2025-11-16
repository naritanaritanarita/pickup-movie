// 電卓の状態型定義
export interface CalculatorState {
  currentValue: string;
  previousValue: string;
  operator: string | null;
  shouldResetDisplay: boolean;
}

// 演算子型
export type Operator = '+' | '-' | '*' | '/';

// ボタンアクション型
export type ButtonAction = 'clear' | 'decimal' | 'equals';

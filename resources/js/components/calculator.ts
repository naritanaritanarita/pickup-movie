import { CalculatorState, Operator, ButtonAction } from '@/types/calculator';

export class Calculator {
  private state: CalculatorState;
  private readonly displaySelector: string = '#calc-display';
  private readonly expressionSelector: string = '#calc-expression';

  constructor() {
    this.state = {
      currentValue: '0',
      previousValue: '',
      operator: null,
      shouldResetDisplay: false
    };

    this.init();
  }

  private init(): void {
    // 数字ボタンのイベントリスナー
    document.querySelectorAll('[data-number]').forEach(button => {
      button.addEventListener('click', (e: Event) => {
        const target = e.currentTarget as HTMLElement;
        const number = target.getAttribute('data-number');
        if (number !== null) {
          this.handleNumberInput(number);
        }
      });
    });

    // 演算子ボタンのイベントリスナー
    document.querySelectorAll('[data-operator]').forEach(button => {
      button.addEventListener('click', (e: Event) => {
        const target = e.currentTarget as HTMLElement;
        const operator = target.getAttribute('data-operator') as Operator;
        if (operator) {
          this.handleOperator(operator);
        }
      });
    });

    // アクションボタンのイベントリスナー
    document.querySelectorAll('[data-action]').forEach(button => {
      button.addEventListener('click', (e: Event) => {
        const target = e.currentTarget as HTMLElement;
        const action = target.getAttribute('data-action') as ButtonAction;
        if (action) {
          this.handleAction(action);
        }
      });
    });

    // キーボード入力のサポート
    document.addEventListener('keydown', this.handleKeyboard.bind(this));
  }

  private handleNumberInput(number: string): void {
    if (this.state.shouldResetDisplay) {
      this.state.currentValue = number;
      this.state.shouldResetDisplay = false;
    } else {
      if (this.state.currentValue === '0') {
        this.state.currentValue = number;
      } else {
        this.state.currentValue += number;
      }
    }
    this.updateDisplay();
  }

  private handleOperator(operator: Operator): void {
    if (this.state.operator && this.state.previousValue && !this.state.shouldResetDisplay) {
      // 連続計算の場合は先に計算を実行
      this.calculate();
    }

    this.state.previousValue = this.state.currentValue;
    this.state.operator = operator;
    this.state.shouldResetDisplay = true;
    this.updateExpression();
  }

  private handleAction(action: ButtonAction): void {
    switch (action) {
      case 'clear':
        this.clear();
        break;
      case 'decimal':
        this.addDecimal();
        break;
      case 'equals':
        this.calculate();
        break;
    }
  }

  private handleKeyboard(e: KeyboardEvent): void {
    // 数字キー
    if (e.key >= '0' && e.key <= '9') {
      this.handleNumberInput(e.key);
    }
    // 演算子キー
    else if (e.key === '+' || e.key === '-' || e.key === '*' || e.key === '/') {
      this.handleOperator(e.key as Operator);
    }
    // Enterキー（=）
    else if (e.key === 'Enter' || e.key === '=') {
      e.preventDefault();
      this.calculate();
    }
    // Escapeキー（クリア）
    else if (e.key === 'Escape') {
      this.clear();
    }
    // 小数点
    else if (e.key === '.') {
      this.addDecimal();
    }
    // Backspaceキー
    else if (e.key === 'Backspace') {
      this.backspace();
    }
  }

  private calculate(): void {
    if (!this.state.operator || !this.state.previousValue) {
      return;
    }

    const prev = parseFloat(this.state.previousValue);
    const current = parseFloat(this.state.currentValue);
    let result = 0;

    switch (this.state.operator) {
      case '+':
        result = prev + current;
        break;
      case '-':
        result = prev - current;
        break;
      case '*':
        result = prev * current;
        break;
      case '/':
        if (current === 0) {
          this.state.currentValue = 'Error';
          this.updateDisplay();
          this.state.operator = null;
          this.state.previousValue = '';
          this.state.shouldResetDisplay = true;
          this.updateExpression();
          return;
        }
        result = prev / current;
        break;
    }

    // 結果を丸める（浮動小数点の誤差を防ぐ）
    this.state.currentValue = this.roundResult(result).toString();
    this.state.operator = null;
    this.state.previousValue = '';
    this.state.shouldResetDisplay = true;
    this.updateDisplay();
    this.updateExpression();
  }

  private roundResult(value: number): number {
    // 小数点以下10桁で丸める
    return Math.round(value * 10000000000) / 10000000000;
  }

  private clear(): void {
    this.state.currentValue = '0';
    this.state.previousValue = '';
    this.state.operator = null;
    this.state.shouldResetDisplay = false;
    this.updateDisplay();
    this.updateExpression();
  }

  private addDecimal(): void {
    if (this.state.shouldResetDisplay) {
      this.state.currentValue = '0.';
      this.state.shouldResetDisplay = false;
    } else if (!this.state.currentValue.includes('.')) {
      this.state.currentValue += '.';
    }
    this.updateDisplay();
  }

  private backspace(): void {
    if (this.state.shouldResetDisplay) {
      return;
    }

    if (this.state.currentValue.length > 1) {
      this.state.currentValue = this.state.currentValue.slice(0, -1);
    } else {
      this.state.currentValue = '0';
    }
    this.updateDisplay();
  }

  private updateDisplay(): void {
    const displayElement = document.querySelector(this.displaySelector);
    if (displayElement) {
      displayElement.textContent = this.state.currentValue;
    }
  }

  private updateExpression(): void {
    const expressionElement = document.querySelector(this.expressionSelector);
    if (expressionElement) {
      if (this.state.previousValue && this.state.operator) {
        const operatorSymbol = this.getOperatorSymbol(this.state.operator);
        expressionElement.textContent = `${this.state.previousValue} ${operatorSymbol}`;
      } else {
        expressionElement.textContent = '';
      }
    }
  }

  private getOperatorSymbol(operator: string): string {
    switch (operator) {
      case '+':
        return '+';
      case '-':
        return '−';
      case '*':
        return '×';
      case '/':
        return '÷';
      default:
        return operator;
    }
  }
}
